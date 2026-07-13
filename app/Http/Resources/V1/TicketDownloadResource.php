<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\TicketDownloadLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketDownloadLink
 */
final class TicketDownloadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $state = $this->isExpired()
            ? 'expired'
            : ($this->isLimitReached() ? 'limit_reached' : 'valid');

        return [
            'id' => $this->id,
            'orderId' => $this->order_id,
            'downloadCount' => $this->download_count,
            'maxDownloads' => $this->max_downloads,
            'state' => $state,
            'expiresAt' => new DateTimeResource(resource: $this->expires_at),
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $this->order->id,
                'orderNumber' => $this->order->order_number,
                'fullName' => trim(($this->order->first_name ?? '').' '.($this->order->last_name ?? '')),
                'email' => $this->order->email,
            ]),
            'createdAt' => new DateTimeResource(resource: $this->created_at),
        ];
    }
}
