<?php

use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Display\DisplayController;
use App\Http\Controllers\Api\Display\MappingController;
use App\Http\Controllers\Api\Display\MonitoringController;
use App\Http\Controllers\Api\Display\UtilityController;
use App\Http\Controllers\Api\Display\InpatientRoomController;
use App\Http\Controllers\Api\Polyclinic\PolyclinicController;
use App\Http\Controllers\Api\Polyclinic\PolyclinicDoctorController;
use App\Http\Controllers\Api\Polyclinic\PolyclinicQueueController;

use App\Http\Controllers\Api\Iot\ServerRoomTelemetryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [LoginController::class, 'login'])->middleware('rate_limit_login');

    // ESP32 IoT Server Room Direct Endpoints
    Route::get('/iot/ping', [ServerRoomTelemetryController::class, 'ping']);
    Route::post('/iot/telemetry', [ServerRoomTelemetryController::class, 'telemetry']);
    Route::get('/iot/server-room', [ServerRoomTelemetryController::class, 'getServerRoomStatus']);
    Route::post('/iot/devices/seed-default', [ServerRoomTelemetryController::class, 'seedDefaultDevice']);

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

        // polyclinics
        Route::apiResource('polyclinics', PolyclinicController::class);
        Route::apiResource('polyclinics.doctors', PolyclinicDoctorController::class);
        Route::get('/polyclinics/{polyId}/queue', [PolyclinicQueueController::class, 'index']);
        Route::post('/polyclinics/{polyId}/queue', [PolyclinicQueueController::class, 'store']);
        Route::put('/polyclinics/{polyId}/queue/{id}/status', [PolyclinicQueueController::class, 'updateStatus']);
        Route::post('/polyclinics/{polyId}/queue/{id}/requeue', [PolyclinicQueueController::class, 'requeue']);
        Route::delete('/polyclinics/{polyId}/queue/{id}', [PolyclinicQueueController::class, 'destroy']);
    });
});

