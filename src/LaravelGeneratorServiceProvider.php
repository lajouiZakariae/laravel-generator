<?php

namespace LaravelGenerator;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use LaravelGenerator\Commands\GenerareCustomFactory;
use LaravelGenerator\Commands\GenerareCustomModel;
use LaravelGenerator\Commands\GenerareCustomPolicy;
use LaravelGenerator\Commands\GenerareCustomRequest;
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
        $this->loadRoutesFrom(__DIR__ . "/../routes/laravel-generator-routes.php");
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-generator');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Blade::componentNamespace('LaravelGenerator\\Views\\Components', 'laravel-generator');

        $this->commands([
            GenerateApi::class,
            GenerateCustomController::class,
            GenerateCustomResource::class,
            GenerareCustomModel::class,
            GenerareCustomFactory::class,
            GenerareCustomPolicy::class,
            GenerareCustomRequest::class,
        ]);

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/laravel-generator'),
        ], 'lg-public');
    }
}
