<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Event\CreateEventAction;
use App\Actions\V1\Event\DeleteEventAction;
use App\Actions\V1\Event\PublishEventAction;
use App\Actions\V1\Event\UpdateEventAction;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Events\ResourceChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\Commission;
use App\Http\Requests\V1\Event\StoreEventRequest;
use App\Http\Requests\V1\Event\UpdateEventRequest;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * @group Events
 *
 * APIs for managing events
 */
final class EventController extends Controller
{
    /**
     * List all events
     *
     * @queryParam status Filter by status (draft, published, cancelled, finished). Example: published
     * @queryParam category_id Filter by category ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W3
     * @queryParam organizer_id Filter by organizer ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W2
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *       "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *       "categoryId": "01HXE2K3M4N5P6Q7R8S9T0V1W3",
     *       "venueId": "01HXE2K3M4N5P6Q7R8S9T0V1W4",
     *       "title": "Summer Music Festival",
     *       "slug": "summer-music-festival-2024",
     *       "description": "Amazing event...",
     *       "banner": "https://example.com/banner.jpg",
     *       "startDate": "2024-06-15T18:00:00+00:00",
     *       "endDate": "2024-06-15T23:00:00+00:00",
     *       "maxAttendees": 1000,
     *       "status": "published",
     *       "statusLabel": "Publié",
     *       "ticketTypesCount": 3,
     *       "reviewsCount": 5,
     *       "averageRating": 4.5,
     *       "createdAt": "2024-01-15T10:00:00.000000Z",
     *       "updatedAt": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::query()
            ->with(['category', 'venue', 'organizer'])
            ->withCount(['ticketTypes', 'reviews', 'favoritedBy'])
            ->latest();

        // Client side (unauthenticated): hide events of deactivated organizers.
        // Back-office users still see everything.
        if ($request->user() === null) {
            $query->whereHas('organizer', function (Builder $q): void {
                $q->where('is_active', true);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->input('organizer_id'));
        }

        $events = $query->paginate(15);

        return EventResource::collection($events);
    }

    /**
     * Store Event
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Event created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "title": "Summer Music Festival"
     *   }
     * }
     */
    public function store(StoreEventRequest $request, CreateEventAction $action): JsonResponse
    {
        /** @var array{organizer_id: string, category_id: string, venue_id?: string|null, title: string, slug: string, description: string, banner?: UploadedFile|null, start_date: string, end_date: string, max_attendees?: int|null, is_featured?: bool, location_type?: string} $validated */
        $validated = $request->validated();

        $event = $action->execute($validated);

        ResourceChangedEvent::dispatch('events', 'created', $event->id, $event->title);

        return $this->created(new EventResource($event));
    }

    /**
     * Show a single event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *     "categoryId": "01HXE2K3M4N5P6Q7R8S9T0V1W3",
     *     "venueId": "01HXE2K3M4N5P6Q7R8S9T0V1W4",
     *     "title": "Summer Music Festival",
     *     "slug": "summer-music-festival-2024",
     *     "description": "Join us for an amazing night...",
     *     "banner": "https://example.com/banner.jpg",
     *     "startDate": "2024-06-15T18:00:00+00:00",
     *     "endDate": "2024-06-15T23:00:00+00:00",
     *     "maxAttendees": 1000,
     *     "status": "published",
     *     "statusLabel": "Publié",
     *     "organizer": {...},
     *     "category": {...},
     *     "venue": {...},
     *     "ticketTypes": [...]
     *   }
     * }
     */
    public function show(Event $id): JsonResponse
    {
        $id->load(['organizer', 'category', 'venue', 'ticketTypes', 'reviews'])
            ->loadCount(['ticketTypes', 'reviews']);

        return $this->success(
            data: new EventResource($id),
        );
    }

    /**
     * Update Event
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Event updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "title": "Summer Festival Updated"
     *   }
     * }
     */
    public function update(UpdateEventRequest $request, Event $id, UpdateEventAction $action): JsonResponse
    {
        /** @var array{category_id?: string, venue_id?: string|null, title?: string, slug?: string, description?: string, banner?: UploadedFile|null, start_date?: string, end_date?: string, max_attendees?: int|null, is_featured?: bool, location_type?: string} $validated */
        $validated = $request->validated();

        $data = [
            'event' => $id,
            ...$validated,
        ];

        $updated = $action->execute($data);

        ResourceChangedEvent::dispatch('events', 'updated', $updated->id, $updated->title);

        return $this->success(new EventResource($updated));
    }

    /**
     * Delete Event
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Event $id, DeleteEventAction $action): JsonResponse
    {
        $eventId = $id->id;
        $action->execute(['event' => $id]);

        ResourceChangedEvent::dispatch('events', 'deleted', $eventId);

        return $this->noContent();
    }

    /**
     * Publish Event
     *
     * Publish a draft event.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 200 scenario="Published" {
     *   "message": "Event published successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "status": "published"
     *   }
     * }
     */
    public function publish(Event $id, PublishEventAction $action): JsonResponse
    {
        try {
            $published = $action->execute(['event' => $id]);

            ResourceChangedEvent::dispatch('events', 'updated', $published->id, $published->title);

            return $this->success(new EventResource($published));
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Unpublish an event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Événement dépublié avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "status": "draft",
     *     "statusLabel": "Brouillon"
     *   }
     * }
     */
    public function unpublish(Event $id): JsonResponse
    {
        $id->update(['status' => EventStatus::DRAFT]);

        ResourceChangedEvent::dispatch('events', 'updated', $id->id, $id->title);

        return $this->success(
            data: new EventResource($id),
            message: 'Événement dépublié avec succès.',
        );
    }

    /**
     * Cancel an event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Événement annulé avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "status": "cancelled",
     *     "statusLabel": "Annulé"
     *   }
     * }
     */
    public function cancel(Event $id): JsonResponse
    {
        $id->update(['status' => EventStatus::CANCELLED]);

        ResourceChangedEvent::dispatch('events', 'updated', $id->id, $id->title);

        return $this->success(
            data: new EventResource($id),
            message: 'Événement annulé avec succès.',
        );
    }

    /**
     * Event box-office report
     *
     * Per ticket type: max quantity, sold, scanned (checked-in), not scanned,
     * not sold, revenue and the TicketExpress commission — plus event totals.
     *
     * @urlParam event string required The ID of the event (ULID)
     */
    public function stats(Event $id): JsonResponse
    {
        $ticketTypes = $id->ticketTypes()->get();

        $rows = $ticketTypes->map(function (TicketType $tt): array {
            $sold = Ticket::query()->where('ticket_type_id', $tt->id)->count();
            $scanned = Ticket::query()->where('ticket_type_id', $tt->id)->whereNotNull('checked_in_at')->count();
            $revenue = (float) OrderItem::query()
                ->where('ticket_type_id', $tt->id)
                ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::PAID->value))
                ->sum('subtotal');

            return [
                'ticketTypeId' => $tt->id,
                'name' => $tt->name,
                'price' => (float) $tt->price,
                'quantity' => (int) $tt->quantity,
                'sold' => $sold,
                'scanned' => $scanned,
                'notScanned' => max(0, $sold - $scanned),
                'notSold' => max(0, (int) $tt->quantity - $sold),
                'revenue' => round($revenue, 2),
                'commission' => Commission::amountFor($revenue),
                'netRevenue' => Commission::netFor($revenue),
            ];
        })->values();

        return $this->success([
            'event' => [
                'id' => $id->id,
                'title' => $id->title,
                'maxAttendees' => $id->max_attendees,
            ],
            'commissionRate' => Commission::rate(),
            'ticketTypes' => $rows,
            'totals' => [
                'quantity' => (int) $rows->sum('quantity'),
                'sold' => (int) $rows->sum('sold'),
                'scanned' => (int) $rows->sum('scanned'),
                'notScanned' => (int) $rows->sum('notScanned'),
                'notSold' => (int) $rows->sum('notSold'),
                'revenue' => round((float) $rows->sum('revenue'), 2),
                'commission' => round((float) $rows->sum('commission'), 2),
                'netRevenue' => round((float) $rows->sum('netRevenue'), 2),
            ],
        ]);
    }
}
