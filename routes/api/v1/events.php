<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\EventController;
use Illuminate\Support\Facades\Route;

// Public event listing and details
Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/{id}', [EventController::class, 'show'])->whereUlid('id')->name('events.show');

Route::middleware('auth:sanctum')->group(function (): void {
    // Organizer-manager operations (checked in controller/policy)
    Route::apiResource('events', EventController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['events' => 'id']);

    // Admin and organizer-manager operations (publish/unpublish/cancel)
    Route::middleware('role:admin|super-admin|organizer-manager')->group(function (): void {
        Route::post('events/{id}/publish', [EventController::class, 'publish'])->whereUlid('id')->name('events.publish');
        Route::post('events/{id}/unpublish', [EventController::class, 'unpublish'])->whereUlid('id')->name('events.unpublish');
        Route::post('events/{id}/cancel', [EventController::class, 'cancel'])->whereUlid('id')->name('events.cancel');

        // Box-office report (sold / scanned / revenue / commission per ticket type)
        Route::get('events/{id}/stats', [EventController::class, 'stats'])->whereUlid('id')->name('events.stats');
    });
});
