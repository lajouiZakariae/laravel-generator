<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\ModelGenerator;

class GenerareCustomModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:model {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected ModelGenerator $modelGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->modelGenerator->generate($this->argument('name'));

        $this->info("Model created: {$outputPath}");
    }
}
