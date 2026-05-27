<?php

use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\ActuatorController;
use Illuminate\Support\Facades\Route;

// ESP32 endpoints — no auth (use a shared secret header in production)
Route::post('/sensors',    [SensorController::class,   'store']);
Route::get('/sensors',     [SensorController::class,   'latest']);
Route::get('/actuators',   [ActuatorController::class, 'index']);
Route::post('/actuators',  [ActuatorController::class, 'store']);