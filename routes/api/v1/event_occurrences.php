<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\EventOccurrenceController;
use Illuminate\Support\Facades\Route;

// Event Occurrences CRUD
Route::get('/events/{event}/occurrences', [EventOccurrenceController::class, 'index'])
    ->name('events.occurrences.index');

Route::post('/event-occurrences', [EventOccurrenceController::class, 'store'])
    ->name('event-occurrences.store');

Route::get('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'show'])
    ->name('event-occurrences.show');

Route::put('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'update'])
    ->name('event-occurrences.update');

Route::delete('/event-occurrences/{occurrence}', [EventOccurrenceController::class, 'destroy'])
    ->name('event-occurrences.destroy');
