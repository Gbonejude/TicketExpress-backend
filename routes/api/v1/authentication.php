<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('register', [AuthController::class, 'register']);
    Route::post('register/client', [AuthController::class, 'registerClient']);
    Route::post('register/organizer-manager', [AuthController::class, 'registerOrganizerManager']);
    Route::post('admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:10,1');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
