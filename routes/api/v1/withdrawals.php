<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Custom routes BEFORE apiResource
    Route::get('withdrawals/earnings/{organizer}', [WithdrawalController::class, 'earnings'])->whereUlid('organizer')->name('withdrawals.earnings');
    Route::post('withdrawals/{id}/process', [WithdrawalController::class, 'process'])->whereUlid('id')->name('withdrawals.process');

    Route::apiResource('withdrawals', WithdrawalController::class)
        ->except(['update'])
        ->parameters(['withdrawals' => 'id']);
});
