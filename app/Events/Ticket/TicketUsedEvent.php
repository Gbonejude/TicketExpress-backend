<?php

declare(strict_types=1);

namespace App\Events\Ticket;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketUsedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Ticket $ticket) {}

    /**
     * Broadcast's event name
     */
    public function broadcastAs(): string
    {
        return 'ticket.used';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('tickets.'.$this->ticket->id)];
    }

    /**
     * Data sent back to the client.
     *
     * @return array{ticket: Ticket, message: string}
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => $this->ticket,
            'message' => 'Le ticket a été utilisé pour l\'enregistrement',
        ];
    }
}
