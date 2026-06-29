<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\DateTimeResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ticket
 */
final class TicketResource extends JsonResource
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
            'attendeeName' => $this->attendee_name,
            'attendeeEmail' => $this->attendee_email,
            'qrCode' => $this->qr_code,
            'ticketNumber' => $this->ticket_number,
            'status' => $this->resource->status->value,
            'statusLabel' => $this->resource->status->label(),

            // Check-in information
            'checkedInAt' => $this->checked_in_at ? new DateTimeResource(resource: $this->checked_in_at) : null,
            'checkedInBy' => $this->checked_in_by,
            'isCheckedIn' => $this->isCheckedIn(),
            'accessMethod' => $this->access_method,
            'isOnlineAccess' => $this->isOnlineAccess(),
            'isPhysicalAccess' => $this->isPhysicalAccess(),
            'onlineAccessLink' => $this->online_access_link,

            // Refund information
            'refundReason' => $this->refund_reason,
            'refundedAt' => $this->refunded_at ? new DateTimeResource(resource: $this->refunded_at) : null,

            'ticketType' => new TicketTypeResource($this->whenLoaded('ticketType')),
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
