<?php

declare(strict_types=1);

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderCreatedEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private Order $order) {}

    /**
     * Broadcast's event name
     */
    public function broadcastAs(): string
    {
        return 'order.created';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    /**
     * Data sent back to the client.
     *
     * @return array{order: Order, message: string}
     */
    public function broadcastWith(): array
    {
        return [
            'order' => $this->order,
            'message' => 'Une nouvelle commande a été créée avec succès',
        ];
    }
}
