<?php

declare(strict_types=1);

use App\Http\Controllers\Api\v1\TicketDownloadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ticket Download Routes (Public)
|--------------------------------------------------------------------------
|
| These routes are public and do not require authentication.
| They use secure tokens for access control.
|
*/

// Back-office listing of ticket-download links (screen.tickets).
Route::middleware(['auth:sanctum', 'role_or_permission:super-admin|screen.tickets'])
    ->group(function (): void {
        Route::get('/ticket-downloads', [TicketDownloadController::class, 'index'])->name('ticket-downloads.index');
    });

// Download ticket PDF (public with token)
Route::get('/tickets/download/{token}', [TicketDownloadController::class, 'downloadPdf'])
    ->name('tickets.download.pdf');

// Download QR code image for a specific ticket (public with token)
Route::get('/tickets/qr/{token}/{ticketId}', [TicketDownloadController::class, 'downloadQrImage'])
    ->name('tickets.download.qr');
