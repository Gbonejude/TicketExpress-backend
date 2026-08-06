<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\EventCheckInController;
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

/*
 * Contrôle d'accès d'un événement.
 *
 * Les trois rôles qui tiennent un portique : super-admin, admin et
 * organizer-manager. Le contrôleur restreint ensuite l'organisateur à ses
 * propres événements — le rôle dit qui peut scanner, pas quoi.
 */
Route::middleware(['auth:sanctum', 'role:admin|super-admin|organizer-manager'])
    ->group(function (): void {
        Route::post('events/{event}/tickets/validate', [EventCheckInController::class, 'store'])
            ->whereUlid('event')
            ->name('events.tickets.validate');

        Route::get('events/{event}/check-ins', [EventCheckInController::class, 'index'])
            ->whereUlid('event')
            ->name('events.check-ins.index');
    });
