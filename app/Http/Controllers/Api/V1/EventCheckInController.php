<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Ticket\ValidateEventTicketAction;
use App\Events\ResourceChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TicketResource;
use App\Models\CheckIn;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Support\CheckInWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Ticket validation
 *
 * Contrôle d'accès à l'entrée d'un événement : on lit un code, on répond
 * « entrez » ou « refusé, voici pourquoi ».
 *
 * Ouvert à l'administration et à l'organisateur de l'événement. L'organisateur
 * n'accède qu'à ses propres événements : c'est lui qui tient le portique, mais
 * les billets d'un confrère ne le concernent pas.
 *
 * @authenticated
 */
final class EventCheckInController extends Controller
{
    public function __construct(
        private readonly ValidateEventTicketAction $validateAction,
    ) {}

    /**
     * Validate a ticket for an event
     *
     * @urlParam event string required The event ULID.
     *
     * @bodyParam code string required Ticket number or scanned QR payload. Example: TKT-2026-0001
     *
     * @response 200 scenario="Entrée autorisée" {
     *   "success": true,
     *   "message": "Billet validé. Entrée autorisée.",
     *   "data": {
     *     "result": "ok",
     *     "ticket": {"ticketNumber": "TKT-2026-0001", "attendeeName": "Komi CREPPY"}
     *   }
     * }
     * @response 422 scenario="Déjà passé" {
     *   "success": false,
     *   "message": "Billet déjà validé le 12/08/2026 à 19:42 par Admin TicketExpress.",
     *   "errors": {"result": "already_used", "ticket": {"ticketNumber": "TKT-2026-0001"}}
     * }
     * @response 422 scenario="Portique fermé" {
     *   "success": false,
     *   "message": "Trop tôt : le contrôle d'accès de « Afro Vibes » ouvre le 12/08/2026 à 16:00.",
     *   "errors": {"result": "outside_window", "ticket": {"ticketNumber": "TKT-2026-0001"}}
     * }
     */
    public function store(Request $request, string $eventId): JsonResponse
    {
        $event = Event::findOrFail($eventId);

        if (! $this->mayValidate($request->user(), $event)) {
            return $this->error(
                message: 'Vous ne pouvez valider que les billets de vos propres événements.',
                status: 403,
            );
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $outcome = $this->validateAction->execute([
            'event' => $event,
            'code' => $validated['code'],
            'agent' => $request->user(),
            // Utile quand plusieurs portiques scannent en parallèle : on sait
            // depuis quel poste une entrée a été enregistrée.
            'device' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        $payload = [
            'result' => $outcome['result'],
            'ticket' => $outcome['ticket'] === null
                ? null
                : new TicketResource($outcome['ticket']),
        ];

        if ($outcome['result'] !== ValidateEventTicketAction::RESULT_OK) {
            // 422 pour tous les refus, y compris « inconnu » : le portique
            // n'attend qu'une chose, savoir si la personne entre ou non, et le
            // motif est dans le message.
            return $this->error(
                message: $outcome['message'],
                status: 422,
                errors: $payload,
            );
        }

        ResourceChangedEvent::dispatchQuietly('tickets', 'checked-in', $outcome['ticket']->id);

        return $this->success(
            data: $payload,
            message: $outcome['message'],
        );
    }

    /**
     * Recent check-ins for an event
     *
     * Les dernières entrées enregistrées, pour que l'agent voie défiler ce qu'il
     * vient de scanner et repère une erreur tout de suite.
     *
     * @urlParam event string required The event ULID.
     * @queryParam limit int How many entries to return (max 50). Example: 20
     */
    public function index(Request $request, string $eventId): JsonResponse
    {
        $event = Event::findOrFail($eventId);

        if (! $this->mayValidate($request->user(), $event)) {
            return $this->error(message: 'Accès refusé.', status: 403);
        }

        $limit = min(max((int) $request->input('limit', 20), 1), 50);

        $ticketIds = Ticket::query()
            ->whereHas('ticketType', fn ($q) => $q->where('event_id', $event->id))
            ->pluck('id');

        $checkIns = CheckIn::query()
            ->with(['ticket.ticketType', 'scannedByUser'])
            ->whereIn('ticket_id', $ticketIds)
            ->latest('scanned_at')
            ->limit($limit)
            ->get()
            ->map(static fn (CheckIn $checkIn): array => [
                'id' => $checkIn->id,
                'ticketNumber' => $checkIn->ticket?->ticket_number,
                'attendeeName' => $checkIn->ticket?->attendee_name,
                'ticketTypeName' => $checkIn->ticket?->ticketType?->name,
                'scannedAt' => $checkIn->scanned_at?->toIso8601String(),
                'scannedBy' => $checkIn->scannedByUser === null
                    ? null
                    : $checkIn->scannedByUser->first_name.' '.$checkIn->scannedByUser->last_name,
            ]);

        return $this->success([
            'eventId' => $event->id,
            // L'état du portique voyage avec l'historique : le panneau le charge
            // déjà à l'ouverture, et l'agent voit « fermé jusqu'à 18:00 » avant
            // de scanner plutôt qu'après le premier refus.
            'window' => CheckInWindow::state($event),
            'checkIns' => $checkIns,
        ]);
    }

    /**
     * Administration, ou l'organisateur propriétaire de l'événement.
     */
    private function mayValidate(?User $user, Event $event): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return $user->organizer !== null
            && (string) $event->organizer_id === (string) $user->organizer->id;
    }
}
