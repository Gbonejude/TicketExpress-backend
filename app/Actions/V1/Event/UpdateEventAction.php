<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Events\Event\EventUpdatedEvent;
use App\Models\Event;
use Illuminate\Http\UploadedFile;

final class UpdateEventAction implements Action
{
    /**
     * @param  array{
     *     event: Event,
     *     category_id?: string,
     *     venue_id?: string|null,
     *     title?: string,
     *     slug?: string,
     *     description?: string,
     *     banner?: UploadedFile|null,
     *     start_date?: string,
     *     end_date?: string,
     *     max_attendees?: int|null,
     *     status?: string
     * }  $data
     */
    public function execute(array $data): Event
    {
        $event = $data['event'];

        $event->update(
            collect($data)->except(['event', 'banner'])->toArray()
        );

        if (($data['banner'] ?? null) instanceof UploadedFile) {
            $event->addMedia($data['banner'])
                ->toMediaCollection('events');
        }

        $event = $event->fresh();

        // Dispatch EventUpdatedEvent
        event(new EventUpdatedEvent($event));

        return $event;
    }
}
