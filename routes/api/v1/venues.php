<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\VenueController;
use Illuminate\Support\Facades\Route;

// Public venue listing and details
Route::get('venues', [VenueController::class, 'index'])->name('venues.index');
Route::get('venues/{id}', [VenueController::class, 'show'])->whereUlid('id')->name('venues.show');

// Protected venue management (create, update, delete)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|admin|screen.venues'])
    ->group(function (): void {
        Route::apiResource('venues', VenueController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['venues' => 'id']);
    });
