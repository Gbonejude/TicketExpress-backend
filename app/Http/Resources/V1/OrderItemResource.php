<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
final class OrderItemResource extends JsonResource
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
            'orderId' => $this->order_id,
            'ticketTypeId' => $this->ticket_type_id,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'ticketType' => new TicketTypeResource($this->whenLoaded('ticketType')),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
        ];
    }
}
