<?php

namespace LaravelGenerator\Commands;

use Illuminate\Console\Command;
use LaravelGenerator\Generators\PolicyGenerator;

class GenerareCustomPolicy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:policy {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected PolicyGenerator $policyGenerator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $outputPath = $this->policyGenerator->generate($this->argument('name'));

        $this->info("Policy created: {$outputPath}");
    }
}
