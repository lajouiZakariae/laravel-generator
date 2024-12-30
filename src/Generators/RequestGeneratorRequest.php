<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ requestNamespace }}',
                '{{ requestName }}',
                '{{ rulesArrayAsString }}',
            ],
            [
                $rootNamespace,
                $requestNamespace,
                $preparedRequestName,
                $rulesArrayAsString,
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

        return File::get(__DIR__ . '/../stubs/request.stub');
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

        // dump($validationRules);

        $validationRules->each(function (array $rules, string $columnName) use (&$rulesAsText): void {
            $rulesAsString = "[" . collect($rules)->map(fn(string $rule): string => "'$rule'")->implode(", ") . "],\n";

            $rulesLineAsString = "\t\t\t'$columnName' => $rulesAsString";

            $rulesAsText .= "{$rulesLineAsString}";
        });


        // foreach ($validationRules as $fillable) {
        //     $rulesAsText .= "\t\t'" . $fillable . "',\n";
        // }

        $rulesAsText .= "\t\t];";

        return $rulesAsText;
    }
}
