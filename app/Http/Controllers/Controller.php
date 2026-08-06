<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Laravel n'inclut plus ce trait par défaut : sans lui, `$this->authorize()`
    // n'existe pas. Les policies du projet (voir app/Policies) sont ainsi
    // appelables depuis un contrôleur, en complément du filtrage par écran posé
    // sur les routes.
    use ApiResponse, AuthorizesRequests;
}
