<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

// Protected health dashboard (JSON) — restricted to back-office admins.
// Distinct from the public /api/healthz probe (HealthController) used by
// load balancers / uptime monitors.

// TODO: Uncomment when spatie/laravel-health is installed:
// Route::middleware(['auth:sanctum', 'role:manager|super-admin'])
//     ->get('health', HealthCheckJsonResultsController::class)
//     ->name('health');
