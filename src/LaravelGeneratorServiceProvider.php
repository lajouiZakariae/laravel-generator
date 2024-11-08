<?php

namespace LaravelGenerator;

use Illuminate\Support\ServiceProvider;
use LaravelGenerator\Commands\GenerareCustomFactory;
use LaravelGenerator\Commands\GenerareCustomModel;
use LaravelGenerator\Commands\GenerareCustomPolicy;
use LaravelGenerator\Commands\GenerateApi;
use LaravelGenerator\Commands\GenerateCustomResource;
use LaravelGenerator\Commands\GenerateCustomController;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->commands([
            GenerateApi::class,
            GenerateCustomController::class,
            GenerateCustomResource::class,
            GenerareCustomModel::class,
            GenerareCustomFactory::class,
            GenerareCustomPolicy::class,
        ]);
    }
}
