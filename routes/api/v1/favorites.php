<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\FavoriteController;
use Illuminate\Support\Facades\Route;

// Any authenticated participant can favorite events.
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('events/{event}/favorite', [FavoriteController::class, 'toggle'])->whereUlid('event')->name('events.favorite');
});
