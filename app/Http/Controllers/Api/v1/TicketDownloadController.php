<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TicketDownloadResource;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

final class TicketDownloadController extends Controller
{
    /**
     * List ticket downloads
     *
     * Back-office listing of the ticket-download links generated per order,
     * with how many times each was downloaded and its validity.
     *
     * @queryParam state string Filter by state (valid, expired, limit_reached). Example: valid
     * @queryParam search string Match the order number. Example: ORD-2026
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $now = now();

        $query = TicketDownloadLink::query()
            ->with('order')
            ->latest();

        match ($request->input('state')) {
            'valid' => $query->where('expires_at', '>', $now)->whereColumn('download_count', '<', 'max_downloads'),
            'expired' => $query->where('expires_at', '<=', $now),
            'limit_reached' => $query->whereColumn('download_count', '>=', 'max_downloads'),
            default => null,
        };

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->whereHas('order', function (Builder $q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        return TicketDownloadResource::collection($query->paginate(15));
    }
    /**
     * Download ticket PDF.
     */
    public function downloadPdf(string $token): Response
    {
        $link = TicketDownloadLink::where('token', $token)->firstOrFail();

        // Check expiration
        if ($link->isExpired()) {
            abort(410, 'Le lien de téléchargement a expiré.');
        }

        // Check download limit
        if ($link->isLimitReached()) {
            abort(429, 'Limite de téléchargements atteinte.');
        }

        // Load order with relations
        $order = $link->order->load(['tickets.ticketType.event', 'user']);

        // Generate PDF on-the-fly
        $pdf = Pdf::loadView('pdfs.ticket', ['order' => $order]);

        // Increment download counter
        $link->increment('download_count');

        // Log download
        Log::info('Ticket PDF downloaded', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'token' => $token,
            'download_count' => $link->download_count,
        ]);

        // Return PDF download
        return $pdf->download("ticket-{$order->order_number}.pdf");
    }

    /**
     * Download QR code image for a specific ticket.
     */
    public function downloadQrImage(string $token, string $ticketId): Response
    {
        $link = TicketDownloadLink::where('token', $token)->firstOrFail();

        // Check expiration
        if ($link->isExpired()) {
            abort(410, 'Le lien de téléchargement a expiré.');
        }

        // Check download limit
        if ($link->isLimitReached()) {
            abort(429, 'Limite de téléchargements atteinte.');
        }

        // Find ticket
        $ticket = Ticket::where('id', $ticketId)
            ->where('order_id', $link->order_id)
            ->with(['ticketType.event'])
            ->firstOrFail();

        // Generate QR code image (returns binary PNG data)
        $qrCodeData = QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H') // High error correction (30%)
            ->generate($ticket->qr_code);

        // Ensure we have string data for the response
        if (! is_string($qrCodeData)) {
            abort(500, 'Erreur lors de la génération du QR code.');
        }

        // Increment download counter
        $link->increment('download_count');

        // Log download
        Log::info('QR code image downloaded', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'token' => $token,
        ]);

        // Return PNG image
        return response($qrCodeData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"qr-{$ticket->ticket_number}.png\"");
    }
}
