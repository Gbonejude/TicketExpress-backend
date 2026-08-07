<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Event;
use App\Support\CheckInWindow;
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
            'eventType' => $this->event_type?->value,
            'eventTypeLabel' => $this->event_type?->label(),
            'onlineUrl' => $this->online_url,
            'refundAllowed' => (bool) $this->refund_allowed,
            'refundDaysBefore' => (int) $this->refund_days_before,

            // Marges propres à l'événement : `null` signifie « hérite de la
            // plateforme », et le formulaire doit pouvoir afficher ce vide tel
            // quel plutôt qu'un zéro qui fermerait le portique à l'heure pile.
            'checkinOpenHoursBefore' => $this->checkin_open_hours_before,
            'checkinCloseHoursAfter' => $this->checkin_close_hours_after,

            // La fenêtre effective, marges héritées comprises — ce que le
            // portique appliquera réellement. Sans garde de nullité :
            // `start_date` est NOT NULL en base, et toutes les requêtes qui
            // alimentent cette ressource chargent l'événement en entier.
            'checkinWindow' => CheckInWindow::state($this->resource),
            'organizer' => new OrganizerResource($this->whenLoaded('organizer')),
            'category' => new EventCategoryResource($this->whenLoaded('category')),
            'venue' => new VenueResource($this->whenLoaded('venue')),
            'ticketTypes' => TicketTypeResource::collection($this->whenLoaded('ticketTypes')),
            'ticketTypesCount' => $this->whenCounted('ticketTypes'),
            'favoritesCount' => $this->whenCounted('favoritedBy'),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
