<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EventCategory
 */
final class EventCategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
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
