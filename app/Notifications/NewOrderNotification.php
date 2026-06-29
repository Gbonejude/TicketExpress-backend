<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

final class NewOrderNotification extends Notification
{
    use Queueable;

    public string $action;

    public string $content;

    public Model $order;

    public string $url;

    public function __construct(string $content, string $action, string $url, Model $order)
    {
        $this->content = $content;
        $this->action = $action;
        $this->url = $url;
        $this->order = $order;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'content' => $this->content,
            'action' => $this->action,
            'url' => $this->url,
            'order' => $this->order,
        ];
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }
}
