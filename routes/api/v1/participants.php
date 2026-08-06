<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ParticipantController;
use Illuminate\Support\Facades\Route;

/*
 * Participants (back-office).
 *
 * Rattaché à l'écran des utilisateurs : qui administre les comptes voit aussi
 * les achats. Lecture seule — un participant se modifie depuis Gestion des
 * Utilisateurs, pas ici.
 */
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.users'])
    ->group(function (): void {
        Route::get('participants', [ParticipantController::class, 'index'])
            ->name('participants.index');

        Route::get('participants/{id}', [ParticipantController::class, 'show'])
            ->whereUlid('id')
            ->name('participants.show');
    });
