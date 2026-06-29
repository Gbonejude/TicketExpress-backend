<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Enums\EventStatus;
use App\Events\Event\EventUnpublishedEvent;
use App\Models\Event;

final class UnpublishEventAction implements Action
{
    /**
     * Unpublish an event (change status from published to draft).
     *
     * @param  array{event: Event}  $data
     */
    public function execute(array $data): Event
    {
        /** @var Event $event */
        $event = $data['event'];

        /** @var EventStatus $currentStatus */
        $currentStatus = $event->getAttribute('status');

        if ($currentStatus !== EventStatus::PUBLISHED) {
            throw new \DomainException('Seuls les événements publiés peuvent être dépubliés.');
        }

        $event->update(['status' => EventStatus::DRAFT]);

        $event = $event->fresh();

        // Dispatch EventUnpublishedEvent
        event(new EventUnpublishedEvent($event));

        return $event;
    }
}
