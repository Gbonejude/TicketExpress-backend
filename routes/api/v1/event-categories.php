<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\EventCategoryController;
use Illuminate\Support\Facades\Route;

// Public category listing and details
Route::get('categories', [EventCategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{id}', [EventCategoryController::class, 'show'])->whereUlid('id')->name('categories.show');

// Protected category management (create, update, delete)
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|admin|screen.categories'])
    ->group(function (): void {
        Route::apiResource('categories', EventCategoryController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['categories' => 'id']);
    });
