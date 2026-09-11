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
            // Format français : « ven. 11 sept. 2026, 11h28 ». `toDayDateTimeString()`
            // rendait un format anglais fixe (« Fri, Sep 11, 2026 11:28 AM »),
            // insensible à la locale ; `isoFormat` la respecte. `copy()` pour ne pas
            // altérer l'instance partagée (dont dépend `humanDiff`).
            'human' => $this->resource->copy()->locale('fr')->isoFormat('ddd D MMM YYYY, HH[h]mm'),
        ];
    }
}
