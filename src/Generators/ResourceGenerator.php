<?php


namespace LaravelGenerator\Generators;

use File;

class ResourceGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($resourceName): string
    {
        $preparedResourceName = str($resourceName)->endsWith('Resource') ? $resourceName : "{$resourceName}Resource";

        $rootNamespace = 'App\\';

        $resourceNamespace = "{$rootNamespace}Http\\Resources";

        $template = str()->replace(
            [
                '{{ resourceNamespace }}',
                '{{ resourceName }}',
            ],
            [
                $resourceNamespace,
                $preparedResourceName,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = app_path('Http/Resources');

        $outputPath = "{$outputDirectory}/{$preparedResourceName}.php";

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
        if (File::exists(resource_path('stubs/resource.stub'))) {
            return File::get(resource_path('stubs/resource.stub'));
        }

        return File::get(__DIR__ . '/../stubs/resource.stub');
    }
}
