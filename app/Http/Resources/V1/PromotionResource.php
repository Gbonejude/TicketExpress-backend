<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A promotion is a reduced price applied to a ticket type for a date range.
 * It is stored directly on the ticket type (promotional_price + window).
 *
 * @mixin TicketType
 */
final class PromotionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ticketTypeId' => $this->id,
            'eventId' => $this->event_id,
            'eventTitle' => $this->whenLoaded('event', fn () => $this->event->title),
            'ticketTypeName' => $this->name,
            'price' => $this->price,
            'promotionalPrice' => $this->promotional_price,
            'discountPercentage' => $this->discountPercentage(),
            'state' => $this->promotionState(),
            'hasActivePromotion' => $this->hasActivePromotion(),
            'promotionStartDate' => $this->promotion_start_date
                ? new DateTimeResource(resource: $this->promotion_start_date)
                : null,
            'promotionEndDate' => $this->promotion_end_date
                ? new DateTimeResource(resource: $this->promotion_end_date)
                : null,
        ];
    }

    private function promotionState(): string
    {
        if ($this->promotional_price === null) {
            return 'inactive';
        }

        $now = now();

        if ($this->promotion_start_date && $now->lt($this->promotion_start_date)) {
            return 'scheduled';
        }

        if ($this->promotion_end_date && $now->gt($this->promotion_end_date)) {
            return 'expired';
        }

        return 'active';
    }
}
