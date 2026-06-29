<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Me\MeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->get('me', MeController::class)
    ->name('me');
