<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ContactController;
use Illuminate\Support\Facades\Route;

// Public support form. Throttled like the other unauthenticated POST routes:
// an open mail endpoint is a spam relay otherwise.
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
