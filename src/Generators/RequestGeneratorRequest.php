<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelGenerator\Classes\Column;
use LaravelGenerator\Classes\EnumColumn;
use LaravelGenerator\Classes\Table;

class RequestGeneratorRequest
{
    /**
     * Generate the file from the stub template.
     */
    public function generate(string $requestName, string $action = "store", ?Table $table = null): string
    {
        $suffix = ($action === "update" ? 'Update' : 'Store') . "Request";

        $preparedRequestName = "{$requestName}{$suffix}";

        $rootNamespace = 'App\\';

        $requestNamespace = "{$rootNamespace}Http\\Requests";

        $rulesArrayAsString = $table
            ? $this->generateRulesAsText($action === "update" ? $table->updateValidationRules : $table->validationRules)
            : "return [];";

        $additionalImports = $table
            ? $this->generateAdditionalImports($table->columns)
            : "";

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ requestNamespace }}',
                '{{ requestName }}',
                '{{ rulesArrayAsString }}',
                '{{ additionalImports }}',
            ],
            [
                $rootNamespace,
                $requestNamespace,
                $preparedRequestName,
                $rulesArrayAsString,
                $additionalImports,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = app_path("Http/Requests");

        $outputPath = "{$outputDirectory}/{$preparedRequestName}.php";

        $this->putTheRequestFileContent($outputPath, $template);

        return $outputPath;
    }

    protected function putTheRequestFileContent($outputPath, $template): void
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
        if (File::exists(resource_path('stubs/request.stub'))) {
            return File::get(resource_path('stubs/request.stub'));
        }

        return File::get(__DIR__ . '/../../stubs/request.stub');
    }

    /**
     * Generate the fillable columns text.
     * @param  Collection<string,array<int,string>> $validationRules
     * @return string
     */
    protected function generateRulesAsText(Collection $validationRules): string
    {
        if ($validationRules->isEmpty()) {
            return "return [];";
        }

        $rulesAsText = "return [\n";

        $validationRules->each(function (array $rules, string $columnName) use (&$rulesAsText): void {
            $rulesReduced = collect($rules)
                ->map(fn(string $rule): string => str($rule)->startsWith('Rule::') ? $rule : "'$rule'")->implode(", ");

            $rulesAsString = "[$rulesReduced],\n";

            $rulesLineAsString = "\t\t\t'$columnName' => $rulesAsString";

            $rulesAsText .= "{$rulesLineAsString}";
        });

        $rulesAsText .= "\t\t];";

        return $rulesAsText;
    }

    protected function generateAdditionalImports(Collection $columns): string
    {
        $imports = $columns
            ->filter(fn(Column|EnumColumn $column): bool => $column instanceof Column)
            ->filter(fn(Column $column): bool => $column->isForeign)
            ->map(fn(Column $column): string => $column->foreign->on)
            ->unique()
            ->map(fn(string $tableName): string => str($tableName)->singular()->camel()->ucfirst())
            ->map(fn(string $tableName): string => "use App\\Models\\$tableName;")
            ->implode("\n");

        return "$imports\n";
    }
}
