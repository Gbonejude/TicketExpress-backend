<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Role\RoleController;
use Illuminate\Support\Facades\Route;

// Role management is super-admin-only (screen.administrators); super-admin
// passes via the Gate::before bypass.
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|permission:screen.administrators'])
    ->group(function (): void {
        Route::apiResource('roles', RoleController::class)
            ->parameters(['roles' => 'role']);
    });
