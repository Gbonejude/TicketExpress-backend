<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\User;

/**
 * Contrat pour les méthodes d'authentification.
 * Implémenter cette interface pour ajouter un nouveau canal (email, Google, etc.).
 */
interface AuthenticationMethod
{
    /**
     * Initier le processus d'authentification (ex: envoyer un OTP, rediriger vers OAuth…).
     *
     * @param  array<string, mixed>  $data
     */
    public function initiate(array $data): mixed;

    /**
     * Valider les credentials et retourner l'utilisateur authentifié ou null.
     *
     * @param  array<string, mixed>  $data
     */
    public function authenticate(array $data): ?User;
}
