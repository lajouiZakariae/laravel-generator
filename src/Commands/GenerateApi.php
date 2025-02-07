<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\ControllerGenerator;
use LaravelGenerator\Generators\FactoryGenerator;
use LaravelGenerator\Generators\MigrationGenerator;
use LaravelGenerator\Generators\ModelGenerator;
use LaravelGenerator\Generators\PolicyGenerator;
use LaravelGenerator\Generators\ResourceGenerator;

class GenerateApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:api {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(
        protected ModelGenerator $modelGenerator,
        protected FactoryGenerator $factoryGenerator,
        protected PolicyGenerator $policyGenerator,
        protected ResourceGenerator $resourceGenerator,
        protected ControllerGenerator $controllerGenerator,
        protected MigrationGenerator   $migrationGenerator,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $modelName = $this->argument('name');

        $outputPath = $this->modelGenerator->generate($modelName);

        $this->info("Model created: {$outputPath}");

        $outputPath = $this->factoryGenerator->generate($modelName);

        $this->info("Factory created: {$outputPath}");

        $outputPath = $this->policyGenerator->generate($modelName);

        $this->info("Policy created: {$outputPath}");

        $outputPath = $this->resourceGenerator->generate($modelName);

        $this->info("Resource created: {$outputPath}");

        $outputPath = $this->controllerGenerator->generate($modelName);

        $this->info("Controller created: {$outputPath}");

        $this->migrationGenerator->generate($modelName);

        $this->info("Migration created");
    }
}
