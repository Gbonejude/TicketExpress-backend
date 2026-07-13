<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\EventOccurrenceController;
use Illuminate\Support\Facades\Route;

// Public reads.
Route::get('/events/{event}/occurrences', [EventOccurrenceController::class, 'index'])
    ->name('events.occurrences.index');

Route::get('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'show'])
    ->name('event-occurrences.show');

// Protected writes (screen.events; super-admin via Gate::before).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.events'])
    ->group(function (): void {
        Route::post('/event-occurrences', [EventOccurrenceController::class, 'store'])
            ->name('event-occurrences.store');

        Route::put('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'update'])
            ->name('event-occurrences.update');

        Route::delete('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'destroy'])
            ->name('event-occurrences.destroy');
    });
