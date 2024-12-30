<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\RequestGenerator;
use LaravelGenerator\Generators\RequestGeneratorRequest;

class GenerareCustomRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:request {name} {--action=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected RequestGeneratorRequest $requestGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->requestGenerator->generate($this->argument('name'), $this->option('action') ?: 'store');

        $this->info("Request created: {$outputPath}");
    }
}
