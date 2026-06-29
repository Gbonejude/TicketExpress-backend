<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Enums\EventStatus;
use App\Events\Event\EventPublishedEvent;
use App\Models\Event;

final class PublishEventAction implements Action
{
    /**
     * Publish an event (change status from draft to published).
     *
     * @param  array{event: Event}  $data
     */
    public function execute(array $data): Event
    {
        /** @var Event $event */
        $event = $data['event'];

        /** @var EventStatus $currentStatus */
        $currentStatus = $event->getAttribute('status');

        if ($currentStatus === EventStatus::PUBLISHED) {
            throw new \DomainException('L\'événement est déjà publié.');
        }

        if ($event->ticketTypes()->count() === 0) {
            throw new \DomainException('Impossible de publier un événement sans types de tickets.');
        }

        $event->update(['status' => EventStatus::PUBLISHED]);

        $event = $event->fresh();

        // Dispatch EventPublishedEvent
        event(new EventPublishedEvent($event));

        return $event;
    }
}
