<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Log;

final class EventObserver
{
    public function created(Event $event): void
    {
        Log::info('Event created', ['event_id' => $event->id]);
        // Dispatch events, invalidate cache, etc.
    }

    public function updated(Event $event): void
    {
        Log::info('Event updated', ['event_id' => $event->id]);
    }

    public function deleted(Event $event): void
    {
        Log::info('Event deleted', ['event_id' => $event->id]);
    }
}
