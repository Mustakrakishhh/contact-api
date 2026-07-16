<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);
Route::get('/metrics', [MetricsController::class, 'index']);
Route::post('/contact', ContactController::class);
