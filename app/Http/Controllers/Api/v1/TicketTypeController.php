<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\TicketType\CreateTicketTypeAction;
use App\Actions\V1\TicketType\DeleteTicketTypeAction;
use App\Actions\V1\TicketType\UpdateTicketTypeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TicketType\StoreTicketTypeRequest;
use App\Http\Requests\V1\TicketType\UpdateTicketTypeRequest;
use App\Http\Resources\V1\TicketTypeResource;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;

/**
 * @group Ticket Types
 *
 * APIs for managing ticket types within events
 */
final class TicketTypeController extends Controller
{
    /**
     * List Ticket Types
     *
     * Get a listing of ticket types for an event.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @apiResourceCollection \App\Http\Resources\V1\TicketTypeResource
     *
     * @apiResourceModel \App\Models\TicketType
     */
    public function index(Event $event): JsonResponse
    {
        $ticketTypes = $event->ticketTypes()->latest()->get();

        return $this->success(TicketTypeResource::collection($ticketTypes));
    }

    /**
     * Store Ticket Type
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 201 scenario="Created" {
     *   "message": "Ticket type created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "eventId": "01jkp5yy...",
     *     "name": "VIP",
     *     "price": 50000
     *   }
     * }
     */
    public function store(StoreTicketTypeRequest $request, Event $event, CreateTicketTypeAction $action): JsonResponse
    {
        /** @var array{name: string, description?: string|null, price: string, quantity: int, sold_quantity?: int, sale_start_date?: string|null, sale_end_date?: string|null} $validated */
        $validated = $request->validated();

        $data = [
            'event_id' => $event->id,
            ...$validated,
        ];

        $ticketType = $action->execute($data);

        return $this->created(new TicketTypeResource($ticketType));
    }

    /**
     * Show Ticket Type (Standalone)
     *
     * Show a ticket type by its ID without requiring the event context.
     *
     * @header Accept-Language en
     *
     * @urlParam id string required The ID of the ticket type (ULID)
     *
     * @apiResource \App\Http\Resources\V1\TicketTypeResource
     *
     * @apiResourceModel \App\Models\TicketType
     */
    public function show(TicketType $id): JsonResponse
    {
        return $this->success(new TicketTypeResource($id));
    }

    /**
     * Show Ticket Type (Event Context)
     *
     * Show a ticket type within an event context.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     * @urlParam ticketType string required The ID of the ticket type (ULID)
     *
     * @apiResource \App\Http\Resources\V1\TicketTypeResource
     *
     * @apiResourceModel \App\Models\TicketType
     */
    public function showInEvent(Event $event, TicketType $ticketType): JsonResponse
    {
        return $this->success(new TicketTypeResource($ticketType));
    }

    /**
     * Update Ticket Type
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     * @urlParam id string required The ID of the ticket type (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Ticket type updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "name": "VIP Updated",
     *     "price": 60000
     *   }
     * }
     */
    public function update(UpdateTicketTypeRequest $request, Event $event, TicketType $id, UpdateTicketTypeAction $action): JsonResponse
    {
        /** @var array{ticketType: TicketType, name?: string, description?: string|null, price?: string, quantity?: int, sale_start_date?: string|null, sale_end_date?: string|null} $data */
        $data = [
            'ticketType' => $id,
            ...$request->validated(),
        ];

        $updated = $action->execute($data);

        return $this->success(new TicketTypeResource($updated));
    }

    /**
     * Delete Ticket Type
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     * @urlParam id string required The ID of the ticket type (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Event $event, TicketType $id, DeleteTicketTypeAction $action): JsonResponse
    {
        $action->execute(['ticketType' => $id]);

        return $this->noContent();
    }
}
