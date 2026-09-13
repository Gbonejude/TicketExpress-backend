<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class EventCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
        public readonly ?string $reason = null,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $reasonText = $this->reason ?: 'Annulé par l\'organisateur';

        return [
            'title' => 'Événement annulé',
            'message' => "L'événement '{$this->event->title}' a été annulé. Motif : {$reasonText}. Vos billets ont été remboursés.",
            'type' => 'error',
            'action' => 'view_tickets',
            'url' => '/tickets',
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'reason' => $this->reason,
            'created_at' => now()->toISOString(),
        ];
    }
}
