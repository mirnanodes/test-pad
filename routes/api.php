<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AccountRequestController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FarmConfigController as AdminFarmConfigController;
use App\Http\Controllers\Admin\RequestLogController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MonitoringController;
use App\Http\Controllers\Owner\AnalysisController;
use App\Http\Controllers\Peternak\DashboardController as PeternakDashboardController;
use App\Http\Controllers\Peternak\ManualInputController;
use App\Http\Controllers\Peternak\ProfileController;
use App\Http\Controllers\IoTController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes - Authentication
Route::post('/login', [LoginController::class, 'login']);
Route::post('/request-account', [AccountRequestController::class, 'submit']);
Route::get('/request-account/status', [AccountRequestController::class, 'checkStatus']);

// IoT endpoint (dapat ditambahkan authentication dengan API key)
Route::post('/iot/sensor-data', [IoTController::class, 'storeSensorData']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Common auth routes
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/me', [LoginController::class, 'me']);

    // Admin routes
    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // User Management
        Route::get('/users', [UserManagementController::class, 'index']);
        Route::post('/users', [UserManagementController::class, 'store']);
        Route::get('/users/{id}', [UserManagementController::class, 'show']);
        Route::put('/users/{id}', [UserManagementController::class, 'update']);
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
        
        // Farm Management
        Route::get('/farms', [AdminFarmConfigController::class, 'index']);
        Route::post('/farms', [AdminFarmConfigController::class, 'store']);
        Route::get('/farms/{id}', [AdminFarmConfigController::class, 'show']);
        Route::put('/farms/{id}', [AdminFarmConfigController::class, 'update']);
        Route::delete('/farms/{id}', [AdminFarmConfigController::class, 'destroy']);
        
        // Farm Configuration
        Route::get('/farms/{id}/config', [AdminFarmConfigController::class, 'getConfig']);
        Route::put('/farms/{id}/config', [AdminFarmConfigController::class, 'updateConfig']);
        
        // Request Log Management
        Route::get('/requests', [RequestLogController::class, 'index']);
        Route::get('/requests/{id}', [RequestLogController::class, 'show']);
        Route::put('/requests/{id}/status', [RequestLogController::class, 'updateStatus']);

        // Farm Configuration
        Route::get('/farm-config', [AdminFarmConfigController::class, 'getFarmConfig']);
        Route::put('/farm-config', [AdminFarmConfigController::class, 'updateFarmConfig']);
        Route::post('/farm-config/reset', [AdminFarmConfigController::class, 'resetConfig']);
    });

    // Owner routes
        Route::prefix('owner')->middleware('role:Owner')->group(function () {
        // Dashboard
        Route::get('/dashboard', [OwnerDashboardController::class, 'index']);
        
        // Farms
        Route::get('/farms', [OwnerDashboardController::class, 'getFarms']);
        Route::get('/farms/{id}', [OwnerDashboardController::class, 'showFarm']);
        
        // Monitoring
        Route::get('/farms/{id}/monitoring', [MonitoringController::class, 'index']);
        Route::get('/farms/{id}/latest-sensor', [MonitoringController::class, 'latestSensor']);
        Route::get('/farms/{id}/sensor-history', [MonitoringController::class, 'sensorHistory']);
        
        // Analysis & Reports
        Route::get('/farms/{id}/analytics', [AnalysisController::class, 'index']);
        Route::get('/farms/{id}/reports', [AnalysisController::class, 'reports']);
        Route::get('/farms/{id}/statistics', [AnalysisController::class, 'statistics']);
        
        // Request tambah kandang
        Route::post('/request-farm', [OwnerDashboardController::class, 'requestAddFarm']);
    });

    // Peternak routes
    Route::prefix('peternak')->middleware('role:Peternak')->group(function () {
        // Dashboard
        Route::get('/dashboard', [PeternakDashboardController::class, 'index']);
        
        // Farm assigned
        Route::get('/farm', [PeternakDashboardController::class, 'getFarm']);
        
        // Manual data input
        Route::get('/manual-data', [ManualInputController::class, 'index']);
        Route::post('/manual-data', [ManualInputController::class, 'store']);
        Route::get('/manual-data/{id}', [ManualInputController::class, 'show']);
        Route::put('/manual-data/{id}', [ManualInputController::class, 'update']);
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
    });

    // Common farm status endpoint (accessible by owner and peternak)
    Route::get('/farms/{id}/status', [IoTController::class, 'getFarmStatus']);
});
