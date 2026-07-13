<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\OrganizerController;
use Illuminate\Support\Facades\Route;

// Public organizer listing and details
Route::get('organizers', [OrganizerController::class, 'index'])->name('organizers.index');
Route::get('organizers/{id}', [OrganizerController::class, 'show'])->whereUlid('id')->name('organizers.show');

// Protected organizer management (create, update, delete, approval workflow)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.organizers'])
    ->group(function (): void {
        Route::post('organizers/{id}/approve', [OrganizerController::class, 'approve'])->whereUlid('id')->name('organizers.approve');
        Route::post('organizers/{id}/reject', [OrganizerController::class, 'reject'])->whereUlid('id')->name('organizers.reject');
        Route::post('organizers/{id}/activate', [OrganizerController::class, 'activate'])->whereUlid('id')->name('organizers.activate');
        Route::post('organizers/{id}/deactivate', [OrganizerController::class, 'deactivate'])->whereUlid('id')->name('organizers.deactivate');

        Route::apiResource('organizers', OrganizerController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['organizers' => 'id']);
    });
