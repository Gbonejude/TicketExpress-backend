<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketType
 */
final class TicketTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $availabilityStatus = $this->resource->availabilityStatus();

        return [
            'id' => $this->id,
            'eventId' => $this->event_id,
            'occurrenceId' => $this->occurrence_id,
            'name' => $this->name,
            'description' => $this->description,
            'locationDetails' => $this->location_details,
            'benefits' => $this->benefits ?? [],
            'isFeatured' => $this->is_featured,
            'sortOrder' => $this->sort_order,

            // Pricing and promotions
            'price' => $this->price,
            'promotionalPrice' => $this->promotional_price,
            'currentPrice' => $this->currentPrice(),
            'hasActivePromotion' => $this->hasActivePromotion(),
            'discountPercentage' => $this->discountPercentage(),
            'promotionStartDate' => $this->promotion_start_date ? new DateTimeResource(resource: $this->promotion_start_date) : null,
            'promotionEndDate' => $this->promotion_end_date ? new DateTimeResource(resource: $this->promotion_end_date) : null,

            'quantity' => $this->quantity,
            'soldQuantity' => $this->sold_quantity,
            'availableQuantity' => $this->remainingTickets(),
            'remainingTickets' => $this->remainingTickets(),
            'soldPercentage' => $this->resource->soldPercentage(),
            'availabilityPercentage' => $this->resource->availablePercentage(),

            // Availability status
            'availabilityStatus' => $availabilityStatus->value,
            'availabilityStatusLabel' => $availabilityStatus->label(),
            'availabilityStatusColor' => $availabilityStatus->color(),
            'availabilityStatusIcon' => $availabilityStatus->icon(),
            'availabilityStatusBadgeClass' => $availabilityStatus->badgeClass(),
            'isAvailableForPurchase' => $availabilityStatus->isAvailableForPurchase(),
            'urgencyLevel' => $availabilityStatus->urgencyLevel(),

            'saleStartDate' => $this->sale_start_date ? new DateTimeResource(resource: $this->sale_start_date) : null,
            'saleEndDate' => $this->sale_end_date ? new DateTimeResource(resource: $this->sale_end_date) : null,

            // Only present where the caller eager-loads it — the order and
            // ticket endpoints do, so "my tickets" can name the event without
            // one extra request per ticket.
            'event' => new EventResource($this->whenLoaded('event')),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
