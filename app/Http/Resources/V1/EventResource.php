<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
final class EventResource extends JsonResource
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
            'organizerId' => $this->organizer_id,
            'categoryId' => $this->category_id,
            'venueId' => $this->venue_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'banner' => $this->resource->banner,
            'bannerThumbnail' => $this->resource->thumbnail,
            'startDate' => $this->start_date ? new DateTimeResource(resource: $this->start_date) : null,
            'endDate' => $this->end_date ? new DateTimeResource(resource: $this->end_date) : null,
            'maxAttendees' => $this->max_attendees,
            'status' => $this->resource->status->value,
            'statusLabel' => $this->resource->status->label(),
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'category' => new EventCategoryResource($this->whenLoaded('category')),
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'ticketTypes' => TicketTypeResource::collection($this->whenLoaded('ticketTypes')),
            'ticketTypesCount' => $this->whenCounted('ticketTypes'),
            'reviewsCount' => $this->whenCounted('reviews'),
            'averageRating' => $this->when(
                $this->relationLoaded('reviews'),
                fn () => round($this->reviews->avg('rating'), 1),
            ),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
