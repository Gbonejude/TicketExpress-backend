<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CouponController;
use Illuminate\Support\Facades\Route;

// Public coupon validation (for checkout)
Route::get('coupons/validate', [CouponController::class, 'validate'])->name('coupons.validate');

// Authenticated coupon listing
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('coupons/{id}', [CouponController::class, 'show'])->whereUlid('id')->name('coupons.show');
});

// Protected coupon management (create, update, delete)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|admin|organizer-manager|screen.coupons'])
    ->group(function (): void {
        Route::apiResource('coupons', CouponController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['coupons' => 'id']);
    });
