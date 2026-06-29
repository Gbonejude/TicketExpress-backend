<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class EventPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Event $event,
        public readonly User $publishedBy,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $publishedByName = trim("{$this->publishedBy->first_name} {$this->publishedBy->last_name}");

        // Utiliser getAttribute pour accéder à la valeur de l'enum
        $eventStatusValue = $this->event->getAttribute('status')->value;

        return [
            'title' => 'Événement publié',
            'message' => "Événement publié : {$this->event->title} par {$publishedByName}",
            'action' => 'view_event',
            'url' => "/admin/events/{$this->event->id}",
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'published_by_name' => $publishedByName,
            'published_by_email' => $this->publishedBy->email,
            'event_status' => $eventStatusValue,
            'published_at' => now()->toISOString(),
        ];
    }
}
