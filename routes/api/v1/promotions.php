<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PromotionController;
use Illuminate\Support\Facades\Route;

// Promotions management (screen.promotions; super-admin via Gate::before).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.promotions'])
    ->group(function (): void {
        Route::get('promotions', [PromotionController::class, 'index'])->name('promotions.index');
        Route::put('promotions/{ticketType}', [PromotionController::class, 'update'])->whereUlid('ticketType')->name('promotions.update');
        Route::delete('promotions/{ticketType}', [PromotionController::class, 'destroy'])->whereUlid('ticketType')->name('promotions.destroy');
    });
