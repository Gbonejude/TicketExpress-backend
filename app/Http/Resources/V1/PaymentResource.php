<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
final class PaymentResource extends JsonResource
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
            'amount' => $this->amount,
            'method' => $this->resource->method->value,
            'methodLabel' => $this->resource->method->label(),
            'transactionReference' => $this->transaction_reference,
            'status' => $this->resource->status->value,
            'statusLabel' => $this->resource->status->label(),
            'paidAt' => $this->paid_at ? new DateTimeResource(resource: $this->paid_at) : null,
            'order' => new OrderResource($this->whenLoaded('order')),
            'createdAt' => new DateTimeResource(
                resource: $this->created_at,
            ),
            'updatedAt' => new DateTimeResource(
                resource: $this->updated_at,
            ),
        ];
    }
}
