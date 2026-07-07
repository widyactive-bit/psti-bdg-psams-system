<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AthleteApiController;
use App\Http\Controllers\API\AttendanceApiController;
use App\Http\Controllers\API\ScheduleApiController;
use Illuminate\Support\Facades\Route;

// Health Check Endpoint (for monitoring/deployment)
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'PSAMS Backend API',
        'environment' => config('app.env'),
    ]);
});

// Public Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Routes (Laravel Sanctum Auth)
Route::middleware('auth:sanctum')->group(function () {
    // Auth profile
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Athlete Info & Stats
    Route::get('/athlete/profile', [AthleteApiController::class, 'profile']);
    Route::get('/athlete/stats', [AthleteApiController::class, 'stats']);
    Route::get('/athlete/achievements', [AthleteApiController::class, 'achievements']);
    Route::post('/athlete/ai-analytics', [AthleteApiController::class, 'getAiAnalytics']);

    // Attendance Geotagging
    Route::post('/attendance/checkin', [AttendanceApiController::class, 'checkin']);
    Route::post('/attendance/checkout', [AttendanceApiController::class, 'checkout']);
    Route::get('/attendance/history', [AttendanceApiController::class, 'history']);

    // Schedules
    Route::get('/schedules', [ScheduleApiController::class, 'index']);
});
