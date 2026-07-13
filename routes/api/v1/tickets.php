<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Route;

// Authenticated ticket management
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('tickets/{ticket}/refund', [TicketController::class, 'refund'])
        ->whereUlid('ticket')
        ->name('tickets.refund');

    Route::post('tickets/{ticket}/check-in', [TicketController::class, 'checkIn'])
        ->whereUlid('ticket')
        ->name('tickets.check-in');
});

// Back-office issued-tickets listing (screen.tickets; super-admin via Gate::before).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.tickets'])
    ->group(function (): void {
        Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    });
