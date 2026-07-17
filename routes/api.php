<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index'])->name('api.health');
Route::get('/metrics', [MetricsController::class, 'index'])->name('api.metrics');
Route::post('/contact', ContactController::class)
    ->middleware('throttle:contact')
    ->name('api.contact');
