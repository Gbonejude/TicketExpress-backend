<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

// Platform settings — super-admin only (screen.administrators).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|permission:screen.administrators'])
    ->group(function (): void {
        Route::get('settings', [SettingsController::class, 'show'])->name('settings.show');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
