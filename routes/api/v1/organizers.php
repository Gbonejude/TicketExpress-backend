<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\OrganizerController;
use Illuminate\Support\Facades\Route;

// Public organizer listing and details
Route::get('organizers', [OrganizerController::class, 'index'])->name('organizers.index');
Route::get('organizers/{id}', [OrganizerController::class, 'show'])->whereUlid('id')->name('organizers.show');

// Protected organizer management (create, update, delete)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.organizers'])
    ->group(function (): void {
        Route::apiResource('organizers', OrganizerController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['organizers' => 'id']);
    });
