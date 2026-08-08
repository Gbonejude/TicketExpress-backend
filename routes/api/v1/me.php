<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Me\MeController;
use App\Http\Controllers\Api\V1\Me\UpdatePasswordController;
use App\Http\Controllers\Api\V1\Me\UpdateProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('me', MeController::class)->name('me');

    // Le compte connecté modifie ses propres informations. `users/{id}` reste
    // l'écran de l'administration, qui désigne quelqu'un d'autre.
    Route::put('me', UpdateProfileController::class)->name('me.update');

    // Le mot de passe a sa propre route : il ne se valide pas comme un nom, et
    // le confondre avec le reste du profil ferait ressaisir l'actuel à chaque
    // correction de numéro de téléphone.
    Route::put('me/password', UpdatePasswordController::class)->name('me.password');
});
