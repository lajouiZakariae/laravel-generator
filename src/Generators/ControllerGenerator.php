<?php


namespace LaravelGenerator\Generators;

use File;

class ControllerGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($controllerName): string
    {

        $preparedControllerName = str($controllerName)->endsWith('Controller') ? $controllerName : "{$controllerName}Controller";

        $controllerPath = str()->replace('\\', '/', $preparedControllerName);

        $subDirectories = str($controllerPath)->explode('/');

        $controllerName = $subDirectories->pop();

        $namespacePath = $subDirectories->implode('\\');

        $rootNamespace = 'App\\';

        $subNamespace = str($namespacePath)->isNotEmpty() ? "\\{$namespacePath}" : '';

        $controllerNamespace = "{$rootNamespace}Http\\Controllers{$subNamespace}";

        $modelName = str($preparedControllerName)->replaceLast('Controller', '');

        $modelPlural = str($modelName)->plural();

        $modelInclude = "{$rootNamespace}Models\\{$modelName}";

        $resourceInclude = "{$rootNamespace}Http\\Resources\\{$modelName}Resource";

        $modelVariableName = str($modelName)->camel()->prepend('$');

        $modelPluralVariableName = str($modelName)->camel()->prepend('$');

        $tableName = str($modelName)->snake()->plural();

        $template = str()->replace(
            [
                '{{ rootNamespace }}',
                '{{ controllerNamespace }}',
                '{{ controllerName }}',
                '{{ modelName }}',
                '{{ modelPlural }}',
                '{{ modelInclude }}',
                '{{ modelVariableName }}',
                '{{ modelPluralVariableName }}',
                '{{ resourceInclude }}',
                '{{ tableName }}'
            ],
            [
                $rootNamespace,
                $controllerNamespace,
                $preparedControllerName,
                $modelName,
                $modelPlural,
                $modelInclude,
                $modelVariableName,
                $modelPluralVariableName,
                $resourceInclude,
                $tableName,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = app_path('Http/Controllers' . (!empty($namespacePath) ? "/{$namespacePath}" : ''));

        $outputPath = "{$outputDirectory}/{$preparedControllerName}.php";

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
        if (File::exists(resource_path('stubs/controller.stub'))) {
            return File::get(resource_path('stubs/controller.stub'));
        }

        return File::get(__DIR__ . '/../stubs/controller.stub');
    }
}
