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
 * Corriger ses propres informations ne passe plus par ici — c'est `me`.
 *
 * Chaque geste porte sa propre permission, et non le seul accès à l'écran :
 * `users.create`, `users.update`, `users.delete` existent depuis toujours et
 * pilotent déjà l'affichage des boutons du back-office. Sans elles ici, cacher
 * « Ajouter un utilisateur » n'aurait fermé que le bouton, pas la route.
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
        Route::get('{id}', [UserController::class, 'show'])->name('show');

        Route::post('', [UserController::class, 'store'])
            ->middleware('role_or_permission:super-admin|users.create')
            ->name('store');

        Route::put('{id}', [UserController::class, 'update'])
            ->middleware('role_or_permission:super-admin|users.update')
            ->name('update');

        Route::delete('{id}', [UserController::class, 'destroy'])
            ->middleware('role_or_permission:super-admin|users.delete')
            ->name('destroy');
    });
