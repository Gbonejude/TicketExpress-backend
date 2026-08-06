<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    // Declared before the `{id}` route so "unread-count" is never read as an id.
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('notifications/{id}/markasread', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::middleware('role:admin|super-admin')->group(function (): void {
        Route::apiResource('notifications', NotificationController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['notifications' => 'id']);
    });
});
