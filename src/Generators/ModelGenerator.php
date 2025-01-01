<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelGenerator\Classes\BoolColumn;
use LaravelGenerator\Classes\Column;
use LaravelGenerator\Classes\EnumColumn;
use LaravelGenerator\Classes\Relation;
use LaravelGenerator\Classes\Table;

class ModelGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate(string $modelName, ?Table $table = null): string
    {
        $rootNamespace = 'App\\';

        $modelNamespace = "{$rootNamespace}Models";

        $factoryNamespace = "\\Database\\Factories\\{$modelName}Factory";

        $fillables = $table
            ? $this->generateFillablesColumnsText($table->fillableColumns)
            : "\n\tprotected \$fillable = [];";

        $relationsAsText = $table
            ? $this->generateRelationsText($table->relations)
            : "";

        $castsAsText = $table
            ? $this->generateCastsText($table)
            : "";

        $additionalImports = $table
            ? $this->generateAdditionalImports($table)
            : "";

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ modelNamespace }}',
                '{{ modelName }}',
                '{{ factoryNamespace }}',
                '{{ fillables }}',
                '{{ relationsAsText }}',
                '{{ additionalImports }}',
                '{{ castsAsText }}',
            ],
            [
                $rootNamespace,
                $modelNamespace,
                $modelName,
                $factoryNamespace,
                $fillables,
                $relationsAsText,
                $additionalImports,
                $castsAsText,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = app_path('Models');

        $outputPath = "{$outputDirectory}/{$modelName}.php";

        $this->putTheControllerFileContent($outputPath, $template);

        return $outputPath;
    }

    protected function putTheControllerFileContent($outputPath, $template): void
    {
        $outputDirectory = dirname($outputPath);

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        file_put_contents($outputPath, $template);
    }

    /**
     * Retrieve the stub file content.
     */
    protected function getStubFileContent(): bool|string
    {
        if (File::exists(resource_path('stubs/model.stub'))) {
            return File::get(resource_path('stubs/model.stub'));
        }

        return File::get(__DIR__ . '/../../stubs/model.stub');
    }

    /**
     * Generate the fillable columns text.
     * @param Collection<int,string> $fillables
     * @return string
     */
    protected function generateFillablesColumnsText(Collection $fillables): string
    {
        if ($fillables->isEmpty()) {
            return "\n\tprotected \$fillable = [];";
        }

        $fillablesColumnsText = "\n\tprotected \$fillable = [\n";

        foreach ($fillables as $fillable) {
            $fillablesColumnsText .= "\t\t'" . $fillable . "',\n";
        }

        $fillablesColumnsText .= "\t];";

        return $fillablesColumnsText;
    }

    protected function generateRelationsText(Collection $relations): string
    {
        if ($relations->isEmpty()) {
            return "";
        }

        $relationsColumnsText = "";

        $relations->each(function (Relation $relation) use (&$relationsColumnsText): void {
            $realtionMethodName = $relation->type === "belongs-to" ? "belongsTo" : "hasMany";

            $returnType = $relation->type === "belongs-to" ? "BelongsTo" : "HasMany";

            $methodName = $relation->getMethodName();

            $singleRelationText = "\n\tpublic function $methodName(): $returnType\n\t{\n\t\treturn \$this->$realtionMethodName({$relation->getModelName()}::class);\n\t}";

            $relationsColumnsText .= $singleRelationText;
        });

        return $relationsColumnsText;
    }

    /**
     * @param \Illuminate\Support\Collection<int,Relation> $relations
     * @return string
     */
    protected function generateAdditionalImports(Table $table): string
    {
        $imports = $table->relations->reduce(function (string $additionalImports, Relation $relation): string {
            return $additionalImports . ($relation->type === "belongs-to"
                ? "use Illuminate\Database\Eloquent\Relations\BelongsTo;"
                : "use Illuminate\Database\Eloquent\Relations\HasMany;");
        }, "");

        $enumImports = $table->columns
            ->filter(fn(Column|EnumColumn|BoolColumn $column): bool => $column instanceof EnumColumn)
            ->map(fn(EnumColumn $column): string => Table::generateEnumName($table->getName(), $column->name))
            ->unique()
            ->map(fn(string $enumName): string => "use App\\Enums\\{$enumName};");

        $imports .= $enumImports->isEmpty() ? "" : "\n" . $enumImports->implode("\n");

        return "$imports\n";
    }

    /**
     * @param \Illuminate\Support\Collection<int,Column|EnumColumn|BoolColumn> $columns
     * @return string
     */
    protected function generateCastsText(Table $table): string
    {
        if ($table->columns->isEmpty()) {
            return "\n\t/**\n\t * Get the attributes that should be cast.\n\t *\n\t * @return array<string, string>\n\t */\n\tprotected function casts(): array \n\t{\n\t\treturn [];\n\t}";
        }

        $castsFunctionText = $table
            ->columns
            ->filter(function (Column|EnumColumn|BoolColumn $column): bool {
                return $column instanceof EnumColumn || $column instanceof BoolColumn;
            })
            ->reduce(function (string $additionalImports, EnumColumn|BoolColumn $column) use ($table): string {
                if ($column instanceof EnumColumn) {
                    $enumName = Table::generateEnumName($table->getName(), $column->name);

                    $castLine = "\t\t\t'{$column->name}' => {$enumName}::class,";

                    return "$additionalImports\n$castLine";
                };

                if ($column instanceof BoolColumn) {
                    $castLine = "\t\t\t'{$column->name}' => 'boolean',";

                    return "$additionalImports\n$castLine";
                }

                return $additionalImports;
            }, "");

        return "\n\t/**\n\t * Get the attributes that should be cast.\n\t *\n\t * @return array<string, string>\n\t */\n\tprotected function casts(): array \n\t{\n\t\treturn [$castsFunctionText\n\t\t];\n\t}";
    }
}
