<?php

use Illuminate\Support\Facades\Route;
use LaravelGenerator\Controllers\ApiGeneratorController;

Route::view('/', 'index');

Route::post('/', ApiGeneratorController::class);