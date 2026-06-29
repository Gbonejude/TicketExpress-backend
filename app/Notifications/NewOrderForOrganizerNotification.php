<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class NewOrderForOrganizerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Order $order,
        public readonly Event $event,
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
        $ticketsCount = $this->order->tickets->count();

        return [
            'title' => 'Nouvelle commande pour votre événement',
            'message' => "Nouvelle commande #{$this->order->order_number} pour l'événement '{$this->event->title}'",
            'action' => 'view_order',
            'url' => "/organizer/orders/{$this->order->id}",
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'customer_name' => trim("{$this->order->first_name} {$this->order->last_name}"),
            'customer_email' => $this->order->email,
            'tickets_count' => $ticketsCount,
            'total_amount' => $this->order->total_amount,
            'created_at' => now()->toISOString(),
        ];
    }
}
