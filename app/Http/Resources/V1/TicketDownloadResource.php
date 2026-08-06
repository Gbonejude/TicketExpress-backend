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
            'order' => $this->whenLoaded('order', fn (): array => [
                'id' => $this->order->id,
                'orderNumber' => $this->order->order_number,
                'fullName' => trim(($this->order->first_name ?? '').' '.($this->order->last_name ?? '')),
                'email' => $this->order->email,
                // Le téléphone plutôt que l'email pour joindre quelqu'un, et
                // l'événement pour savoir de quel billet on parle — les deux
                // manquaient à la liste des téléchargements.
                'phone' => $this->order->phone,
                'ticketsCount' => (int) ($this->order->tickets_count ?? 0),
                'user' => $this->order->relationLoaded('user') && $this->order->user !== null
                    ? [
                        'image' => $this->order->user->getFirstMediaUrl('users'),
                        'thumbnail' => $this->order->user->getFirstMediaUrl('users', 'thumb'),
                    ]
                    : null,
                'events' => $this->order->relationLoaded('items')
                    ? $this->order->items
                        ->map(fn ($item) => $item->ticketType?->event?->title)
                        ->filter()
                        ->unique()
                        ->values()
                    : [],
            ]),
            'createdAt' => new DateTimeResource(resource: $this->created_at),
        ];
    }
}
