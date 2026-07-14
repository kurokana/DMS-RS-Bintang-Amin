<?php

use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Display\DisplayController;
use App\Http\Controllers\Api\Display\MappingController;
use App\Http\Controllers\Api\Display\MonitoringController;
use App\Http\Controllers\Api\Display\UtilityController;
use App\Http\Controllers\Api\Display\InpatientRoomController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [LoginController::class, 'login'])->middleware('rate_limit_login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [LoginController::class, 'logout']);

        // displays
        Route::get('/displays', [DisplayController::class, 'index']);
        Route::post('/displays', [DisplayController::class, 'store'])->middleware('role:admin');
        Route::put('/displays/{id}', [DisplayController::class, 'update'])->middleware('role:admin');

        // mappings
        Route::put('/displays/{id}/mapping', [MappingController::class, 'update'])->middleware('role:admin,operator');

        // monitoring & logs
        Route::get('/monitoring/displays', [MonitoringController::class, 'index']);
        Route::get('/audit-logs', [UtilityController::class, 'auditLogs']);

        // wards & rooms list & sync triggers
        Route::get('/wards', [UtilityController::class, 'wards']);
        Route::get('/rooms', [UtilityController::class, 'rooms']);
        Route::post('/sync/wards', [UtilityController::class, 'syncWards']);
        Route::post('/sync/schedules', [UtilityController::class, 'syncSchedules']);

        // inpatient rooms management (CRUD)
        Route::apiResource('inpatient-rooms', InpatientRoomController::class);
    });
});
