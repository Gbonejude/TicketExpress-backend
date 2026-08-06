<?php

declare(strict_types=1);

namespace App\Events\Ticket;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a ticket is checked in at the venue entrance.
 */
final class TicketCheckedInEvent implements ShouldBroadcast
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
        return 'ticket.checked-in';
    }

    /**
     * Get the data to broadcast.
     *
     * Une poignée de champs, et non la ressource complète.
     *
     * `TicketResource` avec ses relations `order` et `ticketType` chargées tire
     * derrière elle la commande, ses lignes, le type de billet et l'événement —
     * description comprise. La charge dépassait les 10 240 octets que Pusher
     * accepte par message, et chaque scan finissait en `failed_jobs` : la
     * validation passait à l'écran, la diffusion temps réel jamais.
     *
     * Ce que le client fait de ce message est de toute façon un rafraîchissement
     * (`useRealtimeRefresh`), pas une lecture de la charge. Il lui faut de quoi
     * savoir quel billet, pour quel événement — le reste, il le redemande.
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
            'checkedInAt' => $this->ticket->checked_in_at?->toIso8601String(),
            'message' => 'Le ticket a été scanné avec succès',
        ];
    }
}
