<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class CreateEventAction implements Action
{
    /**
     * @param  array{
     *     organizer_id: string,
     *     category_id: string,
     *     venue_id?: string|null,
     *     title: string,
     *     slug: string,
     *     description: string,
     *     banner?: UploadedFile|null,
     *     start_date: string,
     *     end_date: string,
     *     max_attendees?: int|null,
     *     status?: string|null,
     *     ticket_types?: array<int, array{
     *         name: string,
     *         description?: string|null,
     *         price: float,
     *         quantity: int,
     *         sale_start_date?: string|null,
     *         sale_end_date?: string|null
     *     }>|null
     * }  $data
     */
    public function execute(array $data): Event
    {
        return DB::transaction(function () use ($data): Event {
            $ticketTypes = $data['ticket_types'] ?? null;

            $event = Event::create(
                collect($data)->except(['banner', 'ticket_types'])->toArray()
            );

            if (($data['banner'] ?? null) instanceof UploadedFile) {
                $event->addMedia($data['banner'])
                    ->toMediaCollection('events');
            }

            if (! empty($ticketTypes)) {
                foreach ($ticketTypes as $ticketType) {
                    $event->ticketTypes()->create($ticketType);
                }
            }

            return $event->load('ticketTypes');
        });
    }
}
