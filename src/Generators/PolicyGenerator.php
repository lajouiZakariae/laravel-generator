<?php

namespace LaravelGenerator\Generators;

use Illuminate\Support\Facades\File;

class PolicyGenerator
{
    /**
     * Generate the policy file from the stub template.
     */
    public function generate($policyName): string
    {
        $preparedPolicyName = str($policyName)->endsWith('Policy') ? $policyName : "{$policyName}Policy";

        $modelName = str($preparedPolicyName)->beforeLast('Policy');

        $modelVariableName = str($modelName)->lower()->prepend('$');

        $rootNamespace = 'App\\';

        $policiesNamespace = "{$rootNamespace}Policies";

        $modelClassName = "{$rootNamespace}Models\\{$modelName}";

        $template = str()->replace(
            [
                '{{ policiesNamespace }}',
                '{{ modelClassName }}',
                '{{ policyName }}',
                '{{ modelName }}',
                '{{ modelVariableName }}',
            ],
            [
                $policiesNamespace,
                $modelClassName,
                $preparedPolicyName,
                $modelName,
                $modelVariableName,
            ],
            $this->getStubFileContent()
        );

        // Define output path for the generated policy file
        $outputDirectory = app_path('Policies');

        $outputPath = "{$outputDirectory}/{$preparedPolicyName}.php";

        $this->putFileContent($outputPath, $template);

        return $outputPath;
    }

    protected function putFileContent($outputPath, $template): void
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
        if (File::exists(resource_path('stubs/policy.stub'))) {
            return File::get(resource_path('stubs/policy.stub'));
        }

        return File::get(__DIR__ . '/../stubs/policy.stub');
    }
}
