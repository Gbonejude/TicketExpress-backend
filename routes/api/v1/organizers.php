<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\OrganizerController;
use Illuminate\Support\Facades\Route;

// Sa propre fiche. Déclarée avant `organizers/{id}` — la contrainte `whereUlid`
// suffirait à éviter la collision, mais l'ordre rend l'intention lisible.
// Ouverte à tout compte authentifié : le contrôleur répond 404 à qui n'a pas de
// profil organisateur, et ne laisse modifier que la fenêtre de contrôle
// d'accès. L'écran d'administration reste réservé à `screen.organizers`.
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('organizers/me', [OrganizerController::class, 'showMine'])->name('organizers.me.show');
    Route::put('organizers/me', [OrganizerController::class, 'updateMine'])->name('organizers.me.update');
});

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
