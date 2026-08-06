<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PayGateController;
use Illuminate\Support\Facades\Route;

// PayGate confirmation webhook — public (PayGate posts here after payment).
Route::post('payments/paygate/callback', [PayGateController::class, 'callback'])
    ->name('payments.paygate.callback');

// Public checkout payments: a visitor must be able to pay without logging in.
Route::post('payments/initiate', [PayGateController::class, 'initiate'])->name('payments.initiate');
Route::get('payments/{payment}/status', [PayGateController::class, 'status'])->whereUlid('payment')->name('payments.status');

// Back-office payments listing + merchant balance (screen.payments).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.payments'])
    ->group(function (): void {
        Route::get('payments/balance', [PayGateController::class, 'balance'])->name('payments.balance');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->whereUlid('payment')->name('payments.show');
    });
