<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Enums\EventStatus;
use App\Events\Event\EventCancelledEvent;
use App\Models\Event;

final class CancelEventAction implements Action
{
    /**
     * Cancel an event and trigger refunds for all paid orders.
     *
     * @param  array{event: Event, cancellation_reason?: string|null}  $data
     */
    public function execute(array $data): Event
    {
        /** @var Event $event */
        $event = $data['event'];
        $cancellationReason = $data['cancellation_reason'] ?? 'Événement annulé';

        /** @var EventStatus $currentStatus */
        $currentStatus = $event->getAttribute('status');

        if ($currentStatus === EventStatus::CANCELLED) {
            throw new \DomainException('Cet événement est déjà annulé.');
        }

        $event->update(['status' => EventStatus::CANCELLED]);

        $event = $event->fresh();

        // Dispatch EventCancelledEvent (will trigger automatic refunds)
        event(new EventCancelledEvent($event, $cancellationReason));

        return $event;
    }
}
