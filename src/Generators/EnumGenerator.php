<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Facades\File;

class EnumGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($enumName, $enumValues): string
    {
        $enumName = str($enumName)->endsWith("Enum") ? $enumName : "{$enumName}Enum";

        $enumsNamespace = 'App\\Enums';

        $enumValuesAsText = '';

        foreach ($enumValues as $key => $enumValue) {
            $enumValueSnakeCase = str($enumValue)->snake()->upper();
            $enumValueLine = ($key === 0 ? "" : "\t") . "case $enumValueSnakeCase = '$enumValue';" . ($key === count($enumValues) - 1 ? "" : "\n");
            $enumValuesAsText .= $enumValueLine;
        }

        $template = str()->replace(
            [
                '{{ enumsNamespace }}',
                "{{ enumName }}",
                "{{ enumValues }}",
            ],
            [
                $enumsNamespace,
                $enumName,
                $enumValuesAsText,
            ],
            $this->getStubFileContent()
        );

        $outputDirectory = app_path('Enums');

        $outputPath = "{$outputDirectory}/{$enumName}.php";

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
        if (File::exists(resource_path('stubs/enum.stub'))) {
            return File::get(resource_path('stubs/enum.stub'));
        }

        return File::get(__DIR__ . '/../../stubs/enum.stub');
    }
}
