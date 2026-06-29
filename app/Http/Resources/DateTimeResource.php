<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Carbon
 */
final class DateTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'datetime' => $this->toISOString(),
            'humanDiff' => $this->diffForHumans(),
            'human' => $this->toDayDateTimeString(),
        ];
    }
}
