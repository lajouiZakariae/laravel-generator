<?php

use Illuminate\Support\Facades\Route;
use LaravelGenerator\Controllers\ApiGeneratorController;

Route::view('laravelgenerator', 'laravel-generator::index');

Route::post('laravelgenerator', ApiGeneratorController::class);