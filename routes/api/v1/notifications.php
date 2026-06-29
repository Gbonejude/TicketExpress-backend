<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('notifications/{id}/markasread', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::middleware('role:admin|super-admin')->group(function (): void {
        Route::apiResource('notifications', NotificationController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['notifications' => 'id']);
    });
});
