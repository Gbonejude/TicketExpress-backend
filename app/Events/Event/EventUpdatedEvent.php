<?php

declare(strict_types=1);

namespace App\Events\Event;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EventUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Event $event) {}

    /**
     * Broadcast's event name
     */
    public function broadcastAs(): string
    {
        return 'event.updated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('events.'.$this->event->id)];
    }

    /**
     * Data sent back to the client.
     *
     * @return array{event: Event, message: string}
     */
    public function broadcastWith(): array
    {
        return [
            'event' => $this->event,
            'message' => 'Les détails de l\'événement ont été mis à jour',
        ];
    }
}
