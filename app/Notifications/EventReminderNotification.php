<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Event $event,
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
        $startDate = $this->event->start_date ? $this->event->start_date->format('d/m/Y à H:i') : 'bientôt';

        return [
            'title' => 'Rappel : Votre événement approche !',
            'message' => "L'événement '{$this->event->title}' a lieu le {$startDate}. N'oubliez pas vos billets !",
            'type' => 'info',
            'action' => 'view_tickets',
            'url' => '/tickets',
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_slug' => $this->event->slug,
            'created_at' => now()->toISOString(),
        ];
    }
}
