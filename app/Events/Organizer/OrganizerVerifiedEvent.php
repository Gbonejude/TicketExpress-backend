<?php

declare(strict_types=1);

namespace App\Events\Organizer;

use App\Models\Organizer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrganizerVerifiedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Organizer $organizer) {}

    /**
     * Broadcast's event name
     */
    public function broadcastAs(): string
    {
        return 'organizer.verified';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('organizers.'.$this->organizer->id)];
    }

    /**
     * Data sent back to the client.
     *
     * @return array{organizer: Organizer, message: string}
     */
    public function broadcastWith(): array
    {
        return [
            'organizer' => $this->organizer,
            'message' => 'Votre compte organisateur a été vérifié avec succès',
        ];
    }
}
