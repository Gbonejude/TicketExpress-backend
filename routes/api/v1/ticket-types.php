<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\TicketTypeController;
use Illuminate\Support\Facades\Route;

// Public ticket type details and listing by event
Route::get('ticket-types/{id}', [TicketTypeController::class, 'show'])->whereUlid('id')->name('ticket-types.show');
Route::get('events/{event}/ticket-types', [TicketTypeController::class, 'index'])->whereUlid('event')->name('events.ticket-types.index');

// Protected ticket type management (create, update, delete)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.tickets'])
    ->group(function (): void {
        Route::post('events/{event}/ticket-types', [TicketTypeController::class, 'store'])->whereUlid('event')->name('events.ticket-types.store');
        Route::put('events/{event}/ticket-types/{id}', [TicketTypeController::class, 'update'])->whereUlid('event')->whereUlid('id')->name('events.ticket-types.update');
        Route::delete('events/{event}/ticket-types/{id}', [TicketTypeController::class, 'destroy'])->whereUlid('event')->whereUlid('id')->name('events.ticket-types.destroy');
    });
