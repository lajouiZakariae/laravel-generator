<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\ControllerGenerator;

class GenerateCustomController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:controller {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected ControllerGenerator $controllerGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->controllerGenerator->generate($this->argument('name'));

        $this->info("Controller created: {$outputPath}");
    }
}
