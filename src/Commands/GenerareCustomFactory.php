<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\FactoryGenerator;

class GenerareCustomFactory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:factory {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected FactoryGenerator $factoryGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->factoryGenerator->generate($this->argument('name'));

        $this->info("Factory created: {$outputPath}");
    }
}
