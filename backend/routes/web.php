<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::get('/',       fn() => redirect('/dashboard'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/dashboard/live', [DashboardController::class, 'live'])->name('dashboard.live');

// Route::middleware('auth')->group(function () {
    Route::get('/dashboard',           [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/actuator', [DashboardController::class, 'toggleActuator'])->name('actuator.toggle');
    Route::post('/settings',           [SettingsController::class,  'update'])->name('settings.update');
// });