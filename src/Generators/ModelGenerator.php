<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ modelNamespace }}',
                '{{ modelName }}',
                '{{ factoryNamespace }}',
                '{{ fillables }}',
            ],
            [
                $rootNamespace,
                $modelNamespace,
                $modelName,
                $factoryNamespace,
                $fillables,
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

        return File::get(__DIR__ . '/../stubs/model.stub');
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
}
