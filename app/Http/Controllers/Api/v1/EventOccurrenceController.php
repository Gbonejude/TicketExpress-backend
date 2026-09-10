<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\EventOccurrence\CreateEventOccurrenceAction;
use App\Actions\V1\EventOccurrence\DeleteEventOccurrenceAction;
use App\Actions\V1\EventOccurrence\UpdateEventOccurrenceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EventOccurrence\StoreEventOccurrenceRequest;
use App\Http\Requests\V1\EventOccurrence\UpdateEventOccurrenceRequest;
use App\Http\Resources\V1\EventOccurrenceResource;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Support\CatalogueAudience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Event Occurrences
 *
 * APIs for managing event occurrences (multi-date events)
 */
final class EventOccurrenceController extends Controller
{
    /**
     * List Event Occurrences
     *
     * Display a listing of occurrences for an event.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The event ID. Example: 01HXE...
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE...",
     *       "eventId": "01HXE...",
     *       "startDate": "2026-07-31T19:00:00Z",
     *       "endDate": "2026-07-31T23:00:00Z",
     *       "maxAttendees": 5000,
     *       "currentAttendees": 0,
     *       "status": "active",
     *       "notes": "Session du vendredi soir"
     *     }
     *   ]
     * }
     */
    public function index(Request $request, Event $event): AnonymousResourceCollection
    {
        // Même règle que la fiche de l'événement : le côté public ne voit rien
        // d'un événement terminé, ses dates de représentation comprises.
        if (! CatalogueAudience::requestSeesEverything($request)
            && $event->end_date !== null
            && $event->end_date->isPast()) {
            abort(404);
        }

        $occurrences = $event->occurrences()
            ->with('ticketTypes')
            ->orderBy('start_date')
            ->paginate(15);

        return EventOccurrenceResource::collection($occurrences);
    }

    /**
     * Store Event Occurrence
     *
     * Store a newly created occurrence in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Event occurrence created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "eventId": "01jkp5yy...",
     *     "startDate": "2026-07-31T19:00:00Z",
     *     "endDate": "2026-07-31T23:00:00Z"
     *   }
     * }
     */
    public function store(StoreEventOccurrenceRequest $request, CreateEventOccurrenceAction $action): JsonResponse
    {
        /** @var array{event_id: string, start_date: string, end_date: string, max_attendees: int|null, status: string, notes: string|null} $data */
        $data = $request->validated();

        $this->assertOwnsEvent(Event::findOrFail($data['event_id']));

        $occurrence = $action->execute($data);

        return $this->created(new EventOccurrenceResource($occurrence));
    }

    /**
     * Show Event Occurrence
     *
     * Display the specified occurrence.
     *
     * @header Accept-Language en
     *
     * @urlParam occurrence string required The occurrence ID. Example: 01HXE...
     *
     * @apiResource \App\Http\Resources\V1\EventOccurrenceResource
     *
     * @apiResourceModel \App\Models\EventOccurrence
     */
    public function show(EventOccurrence $occurrence): JsonResponse
    {
        $occurrence->load('ticketTypes');

        return $this->success(new EventOccurrenceResource($occurrence));
    }

    /**
     * Update Event Occurrence
     *
     * Update the specified occurrence in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam occurrence string required The occurrence ID. Example: 01HXE...
     *
     * @response 200 scenario="Updated" {
     *   "message": "Event occurrence updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "startDate": "2026-07-31T20:00:00Z"
     *   }
     * }
     */
    public function update(UpdateEventOccurrenceRequest $request, EventOccurrence $occurrence, UpdateEventOccurrenceAction $action): JsonResponse
    {
        $this->assertOwnsEvent($occurrence->event);

        /** @var array{start_date?: string, end_date?: string, max_attendees?: int|null, status?: string, notes?: string|null} $data */
        $data = $request->validated();

        $data['occurrence_id'] = $occurrence->id;

        $updated = $action->execute($data);

        return $this->success(new EventOccurrenceResource($updated));
    }

    /**
     * Delete Event Occurrence
     *
     * Remove the specified occurrence from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam occurrence string required The occurrence ID. Example: 01HXE...
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(EventOccurrence $occurrence, DeleteEventOccurrenceAction $action): JsonResponse
    {
        $this->assertOwnsEvent($occurrence->event);

        $action->execute(['occurrence_id' => $occurrence->id]);

        return $this->noContent();
    }

    /**
     * Un organisateur ne gère que les représentations de ses propres événements.
     *
     * Il n'existe pas de policy dédiée aux occurrences : la règle est celle de
     * l'événement parent. `scopedOrganizerId` vaut `null` pour l'administration
     * (aucune borne) et l'identifiant de l'organisateur pour un gestionnaire —
     * 403 dès que l'événement visé n'est pas le sien.
     */
    private function assertOwnsEvent(Event $event): void
    {
        $scopedOrganizerId = CatalogueAudience::scopedOrganizerId(request());

        if ($scopedOrganizerId !== null && (string) $event->organizer_id !== $scopedOrganizerId) {
            abort(403, 'Vous ne pouvez gérer que les représentations de vos propres événements.');
        }
    }
}
