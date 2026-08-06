<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
 * Retraits.
 *
 * L'écran est ouvert à l'administration et aux organisateurs
 * (screen.withdrawals ; super-admin via Gate::before), et le contrôleur borne
 * ensuite chaque organisateur à ses propres demandes. Ces routes n'étaient
 * gardées que par `auth:sanctum` : n'importe quel compte connecté, participant
 * compris, pouvait lister les versements de tous les organisateurs.
 *
 * Le traitement d'une demande — approuver, rejeter, marquer payé — reste réservé
 * à l'administration : personne ne valide son propre virement.
 */
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.withdrawals'])
    ->group(function (): void {
        // Custom routes BEFORE apiResource
        Route::get('withdrawals/earnings/{organizer}', [WithdrawalController::class, 'earnings'])
            ->whereUlid('organizer')
            ->name('withdrawals.earnings');

        Route::post('withdrawals/{id}/process', [WithdrawalController::class, 'process'])
            ->whereUlid('id')
            ->middleware('role:admin|super-admin')
            ->name('withdrawals.process');

        Route::apiResource('withdrawals', WithdrawalController::class)
            ->except(['update'])
            ->parameters(['withdrawals' => 'id']);
    });
