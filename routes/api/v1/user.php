<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\User\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Gestion des utilisateurs (back-office).
 *
 * Le groupe n'exigeait que d'être connecté. N'importe quel compte — un
 * participant, un organisateur — pouvait donc lister tous les utilisateurs, en
 * créer, en supprimer, et surtout appeler `PUT users/{id}` sur lui-même avec
 * `role: super-admin` : `UpdateUserAction` applique ce champ, et rien ne
 * vérifiait qui le demandait.
 *
 * Le garde est celui de tous les autres écrans d'administration, et le même que
 * `participants` : l'écran des utilisateurs, ou le rôle qui passe outre.
 * Corriger ses propres informations ne passe plus par ici — c'est `me`.
 */

/*
 * La lecture s'ouvre aussi à l'écran des organisateurs.
 *
 * C'est lui qui rattache un dossier à un compte responsable, et sa liste
 * déroulante se remplit d'ici : bornée au seul `screen.users`, un rôle habilité
 * à valider des organisateurs sans administrer les comptes se retrouverait
 * devant un choix vide, sans rien qui explique pourquoi.
 */
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.users|screen.organizers'])
    ->get('users', [UserController::class, 'index'])
    ->name('users.index');

Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.users'])
    ->prefix('users')
    ->name('users.')
    ->group(function (): void {
        Route::post('', [UserController::class, 'store'])->name('store');
        Route::get('{id}', [UserController::class, 'show'])->name('show');
        Route::put('{id}', [UserController::class, 'update'])->name('update');
        Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
    });
