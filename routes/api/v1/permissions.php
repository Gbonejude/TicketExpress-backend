<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Permission\PermissionController;
use Illuminate\Support\Facades\Route;

// SECURITY P0-4 — Permission management is super-admin-only (screen.administrators);
// super-admin passes via the Gate::before bypass.
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|admin|permission:screen.administrators'])
    ->group(function (): void {
        // Custom routes BEFORE apiResource so literal segments take
        // precedence over the {id} placeholder.
        Route::get('permissions/paginate', [PermissionController::class, 'paginate'])->name('permissions.paginate');

        Route::apiResource('permissions', PermissionController::class)
            ->parameters(['permissions' => 'id']);
    });
