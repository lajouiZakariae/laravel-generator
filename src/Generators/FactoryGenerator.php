<?php


namespace LaravelGenerator\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelGenerator\Classes\Table;


class FactoryGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate(string $factoryName, ?Table $table = null): string
    {
        $rootNamespace = 'App\\';

        $preparedFactoryName = str($factoryName)->endsWith('Factory') ? $factoryName : "{$factoryName}Factory";

        $modelName = str($preparedFactoryName)->beforeLast('Factory');

        $modelClassName = "\\{$rootNamespace}Models\\{$modelName}";

        $factoryArrayAsString = $table
            ? $this->generateFactoryArrayAsString($table->factoryColumns)
            : "return [];";

        $template = str()->replace(
            [
                '{{ factoryName }}',
                '{{ modelClassName }}',
                '{{ factoryArrayAsString }}',
            ],
            [
                $preparedFactoryName,
                $modelClassName,
                $factoryArrayAsString,
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

    /**
     * Generate the fillable columns text.
     * @param Collection<string,string> $factories
     * @return string
     */
    protected function generateFactoryArrayAsString(Collection $factories): string
    {
        if ($factories->isEmpty()) {
            return "return [];";
        }

        $factoryArrayAsString = "return [\n";

        $factories->each(function (string $factory, string $name) use (&$factoryArrayAsString): void {
            $factoryArrayAsString .= "\t\t\t'" . $name . "' => " . $factory . ",\n";
        });

        $factoryArrayAsString .= "\t\t];";

        return $factoryArrayAsString;
    }
}
