<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

/*
 * Rapports & statistiques.
 *
 * Ouvert à l'administration et aux organisateurs (screen.dashboard) ; le
 * contrôleur borne ensuite chaque organisateur à ses propres événements, y
 * compris s'il demande explicitement ceux d'un autre.
 */
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.dashboard'])
    ->group(function (): void {
        Route::get('reports/overview', [ReportController::class, 'overview'])
            ->name('reports.overview');

        Route::get('reports/platform', [ReportController::class, 'platform'])
            ->name('reports.platform');

        Route::get('reports/journal', [ReportController::class, 'journal'])
            ->name('reports.journal');

        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.export.pdf');
    });
