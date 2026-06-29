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
use Illuminate\Http\JsonResponse;
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
    public function index(Event $event): AnonymousResourceCollection
    {
        $occurrences = $event->occurrences()
            ->with('ticketTypes')
            ->orderBy('start_date')
            ->get();

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
        $action->execute(['occurrence_id' => $occurrence->id]);

        return $this->noContent();
    }
}
