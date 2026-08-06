<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Ticket\CheckInTicketAction;
use App\Actions\V1\Ticket\RefundTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Ticket\RefundTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Tickets
 *
 * APIs for managing tickets and refunds
 */
final class TicketController extends Controller
{
    public function __construct(
        private readonly RefundTicketAction $refundTicketAction,
        private readonly CheckInTicketAction $checkInTicketAction,
    ) {}

    /**
     * List issued tickets
     *
     * Back-office listing of issued tickets with their status.
     *
     * @queryParam status string Filter by status (valid, used, cancelled, refunded). Example: valid
     * @queryParam event_id string Only tickets for this event. Example: 01HXE...
     * @queryParam search string Match ticket number, attendee name or email. Example: TKT-2024
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // La commande porte les lignes d'achat et son nombre de billets : c'est
        // ce qui permet à la liste d'afficher le montant réellement payé pour ce
        // billet et combien de billets la même commande comptait, sans un appel
        // par ligne.
        $query = Ticket::query()
            ->with([
                'ticketType.event',
                'order' => fn ($q) => $q->with('items')->withCount('tickets'),
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Scanned (checked-in) vs not-yet-scanned tickets.
        if ($request->filled('checked_in')) {
            filter_var($request->input('checked_in'), FILTER_VALIDATE_BOOLEAN)
                ? $query->whereNotNull('checked_in_at')
                : $query->whereNull('checked_in_at');
        }

        if ($request->filled('event_id')) {
            $query->whereHas('ticketType', function (Builder $q) use ($request): void {
                $q->where('event_id', $request->input('event_id'));
            });
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            // Mot par mot, et l'événement compris : on cherche un billet autant
            // par le nom de la soirée que par celui de l'acheteur, et « Komi
            // CREPPY » doit trouver quelqu'un dont le nom tient sur deux mots.
            $words = preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($words as $word) {
                $query->where(function (Builder $group) use ($word): void {
                    $group->where('ticket_number', 'like', "%{$word}%")
                        ->orWhere('attendee_name', 'like', "%{$word}%")
                        ->orWhere('attendee_email', 'like', "%{$word}%")
                        ->orWhereHas('ticketType', function (Builder $type) use ($word): void {
                            $type->where('name', 'like', "%{$word}%")
                                ->orWhereHas('event', function (Builder $event) use ($word): void {
                                    $event->where('title', 'like', "%{$word}%");
                                });
                        });
                });
            }
        }

        return TicketResource::collection($query->paginate(15));
    }

    /**
     * Check in a ticket (scan QR code)
     *
     * Scan and validate a ticket for event entry.
     * - Ticket must be ACTIVE
     * - Cannot scan twice
     * - Records who performed the check-in
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket scanné avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W8",
     *     "ticketNumber": "TKT-2024-001",
     *     "status": "active",
     *     "checkedIn": true,
     *     "checkedInAt": "2024-01-15T10:00:00.000000Z",
     *     "checkedInBy": "Staff #123"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Ce ticket a déjà été scanné."
     * }
     */
    public function checkIn(Request $request, Ticket $ticket): JsonResponse
    {
        // Ce point d'entrée n'avait aucun contrôle d'accès : tout compte connecté
        // pouvait consommer le billet de n'importe qui. La policy le réserve à
        // l'administration et à l'organisateur de l'événement.
        $this->authorize('checkIn', $ticket);

        try {
            $checkedInTicket = $this->checkInTicketAction->execute([
                'ticket' => $ticket,
                // L'agent est celui de la session. `checked_in_by` arrivait du
                // corps de la requête : la trace était donc déclarative, et
                // vide dès que le client ne l'envoyait pas.
                'checked_in_by' => $request->user()?->id,
            ]);

            \App\Events\ResourceChangedEvent::dispatch('tickets', 'checked-in', $checkedInTicket->id);

            return $this->success(
                data: new TicketResource($checkedInTicket),
                message: 'Ticket scanné avec succès.',
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Refund a ticket
     *
     * Request a refund for a ticket. Subject to refund policy:
     * - Must be at least 30 days before event
     * - No coupon was used on the order
     * - Automatic refund if event is cancelled
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket remboursé avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W8",
     *     "ticketNumber": "TKT-2024-001",
     *     "status": "refunded",
     *     "statusLabel": "Remboursé",
     *     "refundReason": "Demande de remboursement client",
     *     "refundedAt": "2024-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Les remboursements doivent être demandés au moins 30 jours avant l'événement."
     * }
     */
    public function refund(RefundTicketRequest $request, Ticket $ticket): JsonResponse
    {
        try {
            $user = $request->user();
            $isAdmin = $user && $user->hasRole('admin');

            $refundedTicket = $this->refundTicketAction->execute([
                'ticket' => $ticket,
                'reason' => $request->input('reason'),
                'force_refund' => $isAdmin && $request->input('force_refund', false),
            ]);

            return $this->success(
                data: new TicketResource($refundedTicket),
                message: 'Ticket remboursé avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }
}
