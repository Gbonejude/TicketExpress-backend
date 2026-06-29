<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Coupon
 */
final class CouponResource extends JsonResource
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
            'code' => $this->code,
            'type' => $this->resource->type->value,
            'typeLabel' => $this->resource->type->label(),
            'value' => $this->value,
            'maxUsage' => $this->max_usage,
            'usedCount' => $this->used_count,
            'remainingUsage' => $this->max_usage - $this->used_count,
            'startDate' => $this->start_date ? new DateTimeResource(resource: $this->start_date) : null,
            'endDate' => $this->end_date ? new DateTimeResource(resource: $this->end_date) : null,
            'isActive' => now()->between($this->start_date, $this->end_date) && $this->used_count < $this->max_usage,
            'events' => EventResource::collection($this->whenLoaded('events')),
            'eventsCount' => $this->whenCounted('events'),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
