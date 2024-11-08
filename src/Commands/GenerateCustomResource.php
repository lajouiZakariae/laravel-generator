<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\ResourceGenerator;

class GenerateCustomResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:resource {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected ResourceGenerator $resourceGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->resourceGenerator->generate($this->argument('name'));

        $this->info("Resource created: {$outputPath}");
    }
}
