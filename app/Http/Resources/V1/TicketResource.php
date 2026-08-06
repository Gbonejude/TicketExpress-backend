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

            // Montant payé pour ce billet. Pris sur la ligne de commande, pas sur
            // le tarif courant du type de billet : une promotion passée depuis
            // l'achat ferait sinon afficher un prix que personne n'a payé.
            'amount' => $this->paidAmount(),

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

    /**
     * Le prix unitaire de la ligne de commande correspondant à ce type de billet.
     *
     * Retombe sur le tarif du type de billet quand la commande n'est pas chargée
     * (l'affichage vaut mieux qu'un tiret), et sur null si rien n'est disponible.
     */
    private function paidAmount(): float|string|null
    {
        if ($this->resource->relationLoaded('order') && $this->order?->relationLoaded('items')) {
            $line = $this->order->items
                ->firstWhere('ticket_type_id', $this->ticket_type_id);

            if ($line !== null) {
                return $line->unit_price;
            }
        }

        if ($this->resource->relationLoaded('ticketType') && $this->ticketType !== null) {
            return $this->ticketType->promotional_price ?? $this->ticketType->price;
        }

        return null;
    }
}
