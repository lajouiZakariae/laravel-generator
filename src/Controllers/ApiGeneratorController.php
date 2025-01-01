<?php

namespace LaravelGenerator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelGenerator\Classes\BoolColumn;
use LaravelGenerator\Classes\Column;
use LaravelGenerator\Classes\EnumColumn;
use LaravelGenerator\Classes\NumericColumn;
use LaravelGenerator\Classes\Relation;
use LaravelGenerator\Classes\StringColumn;
use LaravelGenerator\Classes\Table;
use LaravelGenerator\Generators\EnumGenerator;
use LaravelGenerator\Generators\ModelGenerator;
use LaravelGenerator\Generators\ControllerGenerator;
use LaravelGenerator\Generators\FactoryGenerator;
use LaravelGenerator\Generators\PolicyGenerator;
use LaravelGenerator\Generators\RequestGeneratorRequest;
use LaravelGenerator\Generators\ResourceGenerator;

class ApiGeneratorController
{
    public function __construct(
        protected ModelGenerator $modelGenerator,
        protected FactoryGenerator $factoryGenerator,
        protected PolicyGenerator $policyGenerator,
        protected ResourceGenerator $resourceGenerator,
        protected ControllerGenerator $controllerGenerator,
        protected RequestGeneratorRequest $requestGeneratorRequest,
        protected EnumGenerator $enumGenerator,
    ) {
    }

    public function __invoke(Request $request): array
    {
        return ['messages' => collect($request->all())->map(fn($table): array => $this->generateApiForTable($table))->flatten()->toArray()];
    }

    public function generateApiForTable(array $tableData): array
    {
        /**
         * @var string $tableName
         */
        $tableName = $tableData['table_name'];

        /**
         * @var Collection<int,Column> $columnsCollection
         */
        $columnsCollectionOfArrays = collect($tableData['columns']);

        $columnsCollection = $columnsCollectionOfArrays->map(function (array $columnArray): EnumColumn|NumericColumn|StringColumn|BoolColumn {
            if (in_array($columnArray['type'], ['bigint', 'int', 'float'])) {
                return NumericColumn::fromArray($columnArray);
            }

            if ($columnArray['type'] === 'string') {
                return StringColumn::fromArray($columnArray);
            }

            if ($columnArray['type'] === 'enum') {
                return EnumColumn::fromArray($columnArray);
            }

            if ($columnArray['type'] === 'bool' || $columnArray['type'] === 'boolean') {
                return BoolColumn::fromArray($columnArray);
            }

            return NumericColumn::fromArray($columnArray);
        });

        $table = new Table(
            $tableName,
            $columnsCollection,
        );

        $validationRules = $this->generateValidationRulesForColumn($columnsCollection, 'store', $tableName);

        $table->setValidationRules($validationRules);

        $updateValidationRules = $this->generateValidationRulesForColumn($columnsCollection, 'update', $tableName);

        $table->setUpdateValidationRules($updateValidationRules);

        $fillableColumns = $this->generateFillableColumns($columnsCollection);

        $table->setFillableColumns($fillableColumns);

        $factoryColumns = $this->generateFactoryColumns($columnsCollection, $tableName);

        $table->setFactoryColumns($factoryColumns);

        $relations = collect($tableData['relations'])->map(function (array $relationArray): Relation {
            return Relation::fromArray($relationArray);
        });

        $table->setRelations($relations);

        $modelName = $table->getModelName();

        $successMessages = collect([]);

        // generate factory
        $outputPath = $this->factoryGenerator->generate($modelName, $table);

        $successMessages->push("Factory created: {$outputPath}");

        // generate model
        $outputPath = $this->modelGenerator->generate($modelName, $table);

        $successMessages->push("Model created: {$outputPath}");

        // generate policy
        $outputPath = $this->policyGenerator->generate($modelName);

        $successMessages->push("Policy created: {$outputPath}");

        // generate resource
        $outputPath = $this->resourceGenerator->generate($modelName);

        $successMessages->push("Resource created: {$outputPath}");

        // generate controller
        $outputPath = $this->controllerGenerator->generate($modelName);

        $successMessages->push("Controller created: {$outputPath}");

        // generate store request
        $outputPath = $this->requestGeneratorRequest->generate($modelName, 'store', $table);

        $successMessages->push("Store Request created: {$outputPath}");

        // generate update request
        $outputPath = $this->requestGeneratorRequest->generate($modelName, 'update', $table);

        $successMessages->push("Update Request created: {$outputPath}");

        // generate enums for enum columns
        $enumColumns = $table
            ->columns
            ->filter(fn(Column|EnumColumn|BoolColumn $column): bool => $column instanceof EnumColumn);

        if ($enumColumns->isNotEmpty()) {
            $baseEnumStubFilePath = File::exists(resource_path('stubs/base-enum.stub')) ? resource_path('stubs/base-enum.stub') : __DIR__ . '/../../stubs/base-enum.stub';
            $baseEnumOutputPath = app_path("Traits/BaseEnum.php");

            if (!File::exists($baseEnumOutputPath)) {
                File::makeDirectory(app_path("Traits"), 0755, true, true);
                File::copy($baseEnumStubFilePath, $baseEnumOutputPath);
            }
        }

        $enumColumns
            ->each(function (EnumColumn $column) use ($table, $successMessages): void {
                $outputPath = $this->enumGenerator->generate(Table::generateEnumName($table->getName(), $column->name), $column->enumValues);

                $successMessages->push("Enum created: {$outputPath}");
            });

        return $successMessages->toArray();
    }

    private function generateValidationRulesForColumn(Collection $columnsCollection, string $action = "store", ?string $tableName = null): Collection
    {
        $validationRules = collect([]);

        $columnsCollection->each(function (EnumColumn|NumericColumn|StringColumn|BoolColumn $column) use ($validationRules, $action, $tableName): void {
            if ($column instanceof EnumColumn) {
                $columnValidationRules = [];

                $columnValidationRules[] = $action === 'store' ? $column->isNullable ? 'nullable' : 'required' : 'nullable';

                $enumName = Table::generateEnumName($tableName, $column->name);

                $columnValidationRules[] = "Rule::enum({$enumName}Enum::class)";

                $validationRules->put($column->name, $columnValidationRules);
            }

            if (!$column instanceof EnumColumn && !$column instanceof BoolColumn && !$column->isPrimary) {
                $columnValidationRules = [];

                $columnValidationRules[] = $action === 'store' ? $column->isNullable ? 'nullable' : 'required' : 'nullable';

                if ($column instanceof StringColumn) {
                    $columnValidationRules[] = 'string';

                    $columnValidationRules[] = "max:" . ($column->stringMax ?: 255);
                }

                if ($column instanceof NumericColumn) {
                    if ($column->type === "int" || $column)
                        $columnValidationRules[] = "integer";

                    if ($column->type === "float")
                        $columnValidationRules[] = "numeric";
                }

                if ($column->isForeign) {
                    $columnValidationRules[] = "Rule::exists(" . str($column->foreign->on)->singular()->camel()->ucfirst() . "::class, '" . $column->foreign->references . "')";
                }

                $validationRules->put($column->name, $columnValidationRules);
            }
        });

        return $validationRules;
    }

    private function generateFillableColumns(Collection $columnsCollection): Collection
    {
        return $columnsCollection
            ->filter(fn(Column|EnumColumn|BoolColumn $column): bool => (!$column instanceof EnumColumn && !$column instanceof BoolColumn) ? !$column->isPrimary : true)
            ->map(fn(Column|EnumColumn|BoolColumn $column): string => $column->name);
    }

    private function generateFactoryColumns(Collection $columnsCollection, string $tableName): Collection
    {
        return $columnsCollection
            ->filter(fn(Column|EnumColumn|BoolColumn $column): bool => (!$column instanceof EnumColumn && !$column instanceof BoolColumn) ? !$column->isPrimary : true)
            ->mapWithKeys(function (Column|EnumColumn|BoolColumn $column) use ($tableName): array {
                if ($column instanceof StringColumn) {
                    if ($column->name === 'email') {
                        return [$column->name => 'fake()->email()'];
                    }

                    if ($column->name === 'phone' || $column->name === 'phone_number') {
                        return [$column->name => 'fake()->phoneNumber()'];
                    }

                    return [$column->name => 'fake()->text(' . ($column->stringMax ?: '') . ')'];
                }


                if ($column instanceof NumericColumn) {
                    if ($column->isForeign) {
                        return [$column->name => str($column->foreign->on)->singular()->camel()->ucfirst() . '::factory()'];
                    }

                    return [$column->name => 'fake()->randomNumber()'];
                }

                if ($column instanceof EnumColumn) {
                    return [$column->name => 'fake()->randomElement(' . Table::generateEnumName($tableName, $column->name) . 'Enum::values())'];
                }

                if ($column instanceof BoolColumn) {
                    return [$column->name => 'fake()->boolean()'];
                }

                return [$column->name => ''];
            });
    }
}
