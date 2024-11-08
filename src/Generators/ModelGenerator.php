<?php


namespace LaravelGenerator\Generators;

use File;

class ModelGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($modelName): string
    {
        $rootNamespace = 'App\\';

        $modelNamespace = "{$rootNamespace}Models";

        $factoryNamespace = "\\Database\\Factories\\{$modelName}Factory";

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ modelNamespace }}',
                '{{ modelName }}',
                '{{ factoryNamespace }}',
            ],
            [
                $rootNamespace,
                $modelNamespace,
                $modelName,
                $factoryNamespace,
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
}
