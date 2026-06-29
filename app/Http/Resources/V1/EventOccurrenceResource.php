<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\EventOccurrence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventOccurrence
 */
final class EventOccurrenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'eventId' => $this->event_id,
            'startDate' => $this->start_date ? new DateTimeResource(resource: $this->start_date) : null,
            'endDate' => $this->end_date ? new DateTimeResource(resource: $this->end_date) : null,
            'maxAttendees' => $this->max_attendees,
            'currentAttendees' => $this->current_attendees,
            'remainingCapacity' => $this->remainingCapacity(),
            'availabilityPercentage' => $this->availabilityPercentage(),
            'status' => $this->status,
            'isSoldOut' => $this->isSoldOut(),
            'isActive' => $this->isActive(),
            'notes' => $this->notes,

            // Ticket types for this occurrence (when loaded)
            'ticketTypes' => TicketTypeResource::collection($this->whenLoaded('ticketTypes')),

            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
