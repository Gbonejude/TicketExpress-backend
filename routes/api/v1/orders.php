<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

// Guest checkout supported (no auth required for store)
Route::post('orders', [OrderController::class, 'store'])->name('orders.store');

// Authenticated order management
Route::middleware('auth:sanctum')->group(function (): void {
    // Custom routes BEFORE apiResource
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->whereUlid('order')->name('orders.cancel');

    Route::apiResource('orders', OrderController::class)
        ->only(['index', 'show'])
        ->whereUlid('order');
});
