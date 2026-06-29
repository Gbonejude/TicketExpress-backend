<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

// Public review listing and details
Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('reviews/{id}', [ReviewController::class, 'show'])->whereUlid('id')->name('reviews.show');

// Event-specific reviews (public)
Route::get('events/{eventId}/reviews', [ReviewController::class, 'index'])->whereUlid('eventId')->name('events.reviews.index');

// Authenticated review creation
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('events/{eventId}/reviews', [ReviewController::class, 'store'])->whereUlid('eventId')->name('events.reviews.store');
});

// Protected review management (delete) - admins only
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.reviews'])
    ->group(function (): void {
        Route::apiResource('reviews', ReviewController::class)
            ->only(['destroy'])
            ->parameters(['reviews' => 'id']);
    });
