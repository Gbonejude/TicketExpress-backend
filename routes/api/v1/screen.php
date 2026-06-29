<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Screen\ScreenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:screen.administrators'])
    ->get('screens', ScreenController::class)
    ->name('screens');
