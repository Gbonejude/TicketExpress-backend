<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class EventUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
        public readonly ?string $changesSummary = null,
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
        $summary = $this->changesSummary ?: 'Certains détails de l\'événement ont été modifiés (dates, lieu ou informations).';

        return [
            'title' => 'Mise à jour d\'un événement',
            'message' => "L'événement '{$this->event->title}' a été modifié : {$summary}",
            'type' => 'info',
            'action' => 'view_event',
            'url' => "/events/{$this->event->slug}",
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'created_at' => now()->toISOString(),
        ];
    }
}
