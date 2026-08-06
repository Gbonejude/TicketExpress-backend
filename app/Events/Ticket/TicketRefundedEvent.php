<?php

declare(strict_types=1);

namespace App\Events\Ticket;

use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a ticket is refunded.
 */
final class TicketRefundedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Ticket $ticket,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tickets.{$this->ticket->id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.refunded';
    }

    /**
     * Get the data to broadcast.
     *
     * Même bornage que `TicketCheckedInEvent`, et pour la même raison : la
     * ressource complète avec `order` et `ticketType` chargés dépasse les 10 240
     * octets acceptés par message chez Pusher, et le message part en
     * `failed_jobs`. Le client rafraîchit sur réception, il n'a pas besoin de la
     * charge — seulement de savoir de quel billet il s'agit.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'ticketId' => $this->ticket->id,
            'ticketNumber' => $this->ticket->ticket_number,
            'eventId' => $this->ticket->ticketType?->event_id,
            'status' => $this->ticket->status->value,
            'refundedAt' => $this->ticket->refunded_at?->toIso8601String(),
            'message' => 'Le ticket a été remboursé avec succès',
        ];
    }
}
