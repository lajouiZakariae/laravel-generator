<?php

namespace LaravelGenerator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LaravelGenerator\Classes\Column;
use LaravelGenerator\Classes\EnumColumn;
use LaravelGenerator\Classes\NumericColumn;
use LaravelGenerator\Classes\StringColumn;
use LaravelGenerator\Classes\Table;
use LaravelGenerator\Exceptions\BusinessException;
use LaravelGenerator\Generators\ModelGenerator;
use LaravelGenerator\Generators\ControllerGenerator;
use LaravelGenerator\Generators\FactoryGenerator;
use LaravelGenerator\Generators\PolicyGenerator;
use LaravelGenerator\Generators\ResourceGenerator;

class ApiGeneratorController
{
    public function __construct(
        protected ModelGenerator $modelGenerator,
        protected FactoryGenerator $factoryGenerator,
        protected PolicyGenerator $policyGenerator,
        protected ResourceGenerator $resourceGenerator,
        protected ControllerGenerator $controllerGenerator,
    ) {
    }

    public function __invoke(Request $request): Collection
    {
        $requestBodyAsCollection = collect($request->columns);

        $preparedColumnsData = $requestBodyAsCollection->map(function (array $columnArray): array {
            $typeFormat = $columnArray['type'];

            $type = null;

            $stringMax = null;

            $enumPossibleValues = null;

            if (str($typeFormat)->contains('(') && str($typeFormat)->contains(')')) {
                $type = str($typeFormat)->before('(')->toString();

                if (!in_array($type, ['string', 'int', 'enum'])) {
                    throw new BusinessException('Unsupported Type');
                }

                $paramsAsString = str($typeFormat)->between('(', ')')->toString();

                $paramsCollection = collect(explode(',', $paramsAsString));

                if ($type === "enum") {
                    if ($paramsCollection->isEmpty()) {
                        throw new BusinessException('Enum type must have possible values');
                    }

                    $paramsCollection = $paramsCollection->map(function (string $str): string {
                        return str($str)->trim("'")->toString();
                    });
                }

                $stringMax = in_array($type, ['string', 'int']) && $paramsCollection->isNotEmpty()
                    ? $paramsCollection->first()
                    : null;

                $enumPossibleValues = $type === "enum" ?
                    $paramsCollection->toArray()
                    : null;
            } else {
                $type = $typeFormat;
            }

            return [
                ...$columnArray,
                'type' => $type,
                'stringMax' => $stringMax,
                'possibleValues' => $enumPossibleValues,
            ];
        });

        $columnsCollection = $preparedColumnsData->map(function (array $columnArray): EnumColumn|NumericColumn|StringColumn {
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

        // dump($columnsCollection->toArray());

        $validationRules = $this->generateValidationRulesForColumn($columnsCollection);

        // dump($validationRules->toArray());

        $updateValidationRules = $this->generateValidationRulesForColumn($columnsCollection, 'update');

        // dump($updateValidationRules->toArray());

        $fillableColumns = $this->generateFillableColumns($columnsCollection);

        // dump($fillableColumns->toArray());

        $factoryColumns = $this->generateFactoryColumns($columnsCollection);

        // dump($factoryColumns->toArray());

        $table = new Table(
            $request->table_name,
            $columnsCollection,
            $fillableColumns,
            $factoryColumns,
            $validationRules,
            $updateValidationRules,
        );

        $modelName = $table->getModelName();

        // generate factory
        $outputPath = $this->factoryGenerator->generate($modelName);

        dump("Factory created: {$outputPath}");

        // generate model
        $outputPath = $this->modelGenerator->generate($modelName);

        dump("Model created: {$outputPath}");

        $outputPath = $this->policyGenerator->generate($modelName);

        dump("Policy created: {$outputPath}");

        $outputPath = $this->resourceGenerator->generate($modelName);

        dump("Resource created: {$outputPath}");

        $outputPath = $this->controllerGenerator->generate($modelName);

        dump("Controller created: {$outputPath}");

        dd($table);

        return $columnsCollection;
    }

    private function generateValidationRulesForColumn(Collection $columnsCollection, string $action = "store"): Collection
    {
        $validationRules = collect([]);

        $columnsCollection->each(function (EnumColumn|NumericColumn|StringColumn $column) use ($validationRules, $action): void {
            if (!$column->isPrimary) {
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

                $validationRules->put($column->name, $columnValidationRules);
            }
        });

        return $validationRules;
    }

    private function generateFillableColumns(Collection $columnsCollection): Collection
    {
        return $columnsCollection
            ->filter(fn(Column $column): bool => !$column->isPrimary)
            ->map(fn(Column $column): string => $column->name);
    }

    private function generateFactoryColumns(Collection $columnsCollection): Collection
    {
        return $columnsCollection
            ->filter(fn(Column $column): bool => !$column->isPrimary)
            ->mapWithKeys(function (Column $column, string $key): array {
                if ($column instanceof StringColumn) {
                    if ($column->name === 'email') {
                        return [$column->name => 'fake()->email()'];
                    }

                    if ($column->name === 'phone' || $column->name === 'phone_number') {
                        return [$column->name => 'fake()->phoneNumber()'];
                    }

                    return [$column->name => 'fake()->text(' . ($column->stringMax ?: '') . ')'];
                }

                return [$column->name => ''];
            });
    }
}
