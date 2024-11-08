<?php


namespace LaravelGenerator\Generators;

use File;

class FactoryGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($factoryName): string
    {
        $rootNamespace = 'App\\';

        $preparedFactoryName = str($factoryName)->endsWith('Factory') ? $factoryName : "{$factoryName}Factory";

        $modelName = str($preparedFactoryName)->beforeLast('Factory');

        $modelClassName = "\\{$rootNamespace}Models\\{$modelName}";

        $template = str()->replace(
            [
                '{{ factoryName }}',
                '{{ modelClassName }}',
            ],
            [
                $preparedFactoryName,
                $modelClassName,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = database_path('factories');

        $outputPath = "{$outputDirectory}/{$preparedFactoryName}.php";

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
        if (File::exists(resource_path('stubs/factory.stub'))) {
            return File::get(resource_path('stubs/factory.stub'));
        }

        return File::get(__DIR__ . '/../stubs/factory.stub');
    }
}
