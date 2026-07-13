<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Lightweight real-time signal for the back-office. It carries no sensitive
 * data — only "something changed on <channel>" — so the front simply re-fetches
 * the affected list via the authenticated API. Broadcast on a public channel.
 *
 * ShouldBroadcastNow so it fires immediately without needing a queue worker.
 */
final class ResourceChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $channelName,
        public string $action,
        public ?string $id = null,
        public ?string $label = null,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel($this->channelName)];
    }

    public function broadcastAs(): string
    {
        return 'changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'id' => $this->id,
            'label' => $this->label,
        ];
    }
}
