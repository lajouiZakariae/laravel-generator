<?php

namespace LaravelGenerator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelGenerator\Classes\Column;
use LaravelGenerator\Classes\EnumColumn;
use LaravelGenerator\Classes\NumericColumn;
use LaravelGenerator\Classes\Relation;
use LaravelGenerator\Classes\StringColumn;
use LaravelGenerator\Classes\Table;
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
    ) {
    }

    public function __invoke(Request $request): array
    {
        return ['messages' => collect($request->all())->map(fn($table): array => $this->generateApiForTable($table))->flatten()->toArray()];
    }

    public function generateApiForTable(array $table): array
    {
        /**
         * @var string $tableName
         */
        $tableName = $table['table_name'];

        /**
         * @var Collection<int,Column> $columnsCollection
         */
        $columnsCollectionOfArrays = collect($table['columns']);

        $columnsCollection = $columnsCollectionOfArrays->map(function (array $columnArray): EnumColumn|NumericColumn|StringColumn {
            if (in_array($columnArray['type'], ['bigint', 'int', 'float'])) {
                return NumericColumn::fromArray($columnArray);
            }

            if ($columnArray['type'] === 'string') {
                return StringColumn::fromArray($columnArray);
            }

            if ($columnArray['type'] === 'enum') {
                return EnumColumn::fromArray($columnArray);
            }

            return NumericColumn::fromArray($columnArray);
        });

        $validationRules = $this->generateValidationRulesForColumn($columnsCollection);

        $updateValidationRules = $this->generateValidationRulesForColumn($columnsCollection, 'update');

        $fillableColumns = $this->generateFillableColumns($columnsCollection);

        $factoryColumns = $this->generateFactoryColumns($columnsCollection);

        $relations = collect($table['relations'])->map(function (array $relationArray): Relation {
            return Relation::fromArray($relationArray);
        });

        $table = new Table(
            $tableName,
            $columnsCollection,
            $fillableColumns,
            $factoryColumns,
            $validationRules,
            $updateValidationRules,
            $relations,
        );

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

        return $successMessages->toArray();
    }

    private function generateValidationRulesForColumn(Collection $columnsCollection, string $action = "store"): Collection
    {
        $validationRules = collect([]);

        $columnsCollection->each(function (EnumColumn|NumericColumn|StringColumn $column) use ($validationRules, $action): void {
            if (!$column instanceof EnumColumn && !$column->isPrimary) {
                $columnValidationRules = [];

                $columnValidationRules[] = $action === 'store' ? $column->isNullable ? 'nullable' : 'required' : 'nullable';

                if ($column instanceof StringColumn) {
                    $columnValidationRules[] = 'string';

                    $columnValidationRules[] = "max:" . ($column->stringMax ?: 255);
                }

                if ($column instanceof NumericColumn && !$column->isPrimary) {
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
            ->filter(fn(Column|EnumColumn $column): bool => !$column instanceof EnumColumn && !$column->isPrimary)
            ->map(fn(Column|EnumColumn $column): string => $column->name);
    }

    private function generateFactoryColumns(Collection $columnsCollection): Collection
    {
        return $columnsCollection
            ->filter(fn(Column|EnumColumn $column): bool => !$column instanceof EnumColumn && !$column->isPrimary)
            ->mapWithKeys(function (Column $column): array {
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

                return [$column->name => ''];
            });
    }
}
