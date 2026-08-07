<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TicketDownloadResource;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use App\Support\QrImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

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

        // La commande arrive avec de quoi présenter la ligne : le participant et
        // sa photo, l'événement concerné, le nombre de billets. Le comptage est
        // fait par SQL sur la relation, pas ligne par ligne.
        $query = TicketDownloadLink::query()
            ->with(['order' => fn ($q) => $q
                ->withCount('tickets')
                ->with(['user', 'items.ticketType.event'])])
            ->latest();

        match ($request->input('state')) {
            'valid' => $query->where('expires_at', '>', $now)->whereColumn('download_count', '<', 'max_downloads'),
            'expired' => $query->where('expires_at', '<=', $now),
            'limit_reached' => $query->whereColumn('download_count', '>=', 'max_downloads'),
            default => null,
        };

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            // Mot par mot, sur le n° de commande, le participant et l'événement :
            // « Komi CREPPY » doit trouver, alors que son prénom et son nom sont
            // dans deux colonnes.
            $words = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($words as $word) {
                $query->whereHas('order', function (Builder $q) use ($word): void {
                    $q->where('order_number', 'like', "%{$word}%")
                        ->orWhere('first_name', 'like', "%{$word}%")
                        ->orWhere('last_name', 'like', "%{$word}%")
                        ->orWhere('phone', 'like', "%{$word}%")
                        ->orWhereHas('items.ticketType.event', function (Builder $e) use ($word): void {
                            $e->where('title', 'like', "%{$word}%");
                        });
                });
            }
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

        // Find ticket
        $ticket = Ticket::where('id', $ticketId)
            ->where('order_id', $link->order_id)
            ->with(['ticketType.event'])
            ->firstOrFail();

        // Le compteur n'est volontairement pas incrémenté ici, et la limite pas
        // vérifiée : c'est l'image qu'affiche « Mes billets » à chaque ouverture
        // de la page. La compter comme un téléchargement faisait disparaître le
        // QR du porteur à force de le regarder — alors que le quota existe pour
        // borner la régénération du PDF, qui est l'opération coûteuse.
        //
        // L'expiration, elle, reste opposable : passé la fenêtre, le lien ne
        // rend plus rien, image comprise.
        $qrCodeData = QrImage::png($ticket->qr_code, size: 400, margin: 2);

        Log::info('QR code image served', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'token' => $token,
        ]);

        // `inline` et non `attachment` : la même URL est lue dans un `<img>` sur
        // la page du billet. Le nom de fichier reste posé pour un
        // « enregistrer l'image sous ».
        return response($qrCodeData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "inline; filename=\"qr-{$ticket->ticket_number}.png\"");
    }
}
