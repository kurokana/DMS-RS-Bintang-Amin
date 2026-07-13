<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DisplayService\DisplayServiceController;
use App\Http\Controllers\HealthCheckController;

Route::get('/', function () {
    return view('welcome');
});

// STB Display Service Routes
Route::get('/display/{displayId}/state', [DisplayServiceController::class, 'state']);
Route::post('/display/{displayId}/heartbeat', [DisplayServiceController::class, 'heartbeat']);

// Health Check Route
Route::get('/health', [HealthCheckController::class, 'check']);
