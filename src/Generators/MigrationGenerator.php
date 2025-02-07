<?php


namespace LaravelGenerator\Generators;

use Artisan;

class MigrationGenerator
{
    /**
     * Generate the file from the stub template.
     */
    public function generate($resourceName): void
    {
        $modelPluralVariableName = str($resourceName)->snake()->plural();

        Artisan::call("make:migration create_{$modelPluralVariableName}_table --create=$modelPluralVariableName",[]);
    }
}
