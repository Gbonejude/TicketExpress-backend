<?php

declare(strict_types=1);

namespace App\Actions\V1\EventOccurrence;

use App\Actions\Contracts\Action;
use App\Models\Event;
use App\Models\EventOccurrence;
use Illuminate\Support\Facades\DB;

final class CreateEventOccurrenceAction implements Action
{
    /**
     * Create a new event occurrence
     *
     * @param  array{event_id: string, start_date: string, end_date: string, max_attendees?: int|null, status?: string, notes?: string|null}  $data
     */
    public function execute(array $data): EventOccurrence
    {
        return DB::transaction(function () use ($data) {
            // Verify event exists
            $event = Event::findOrFail($data['event_id']);

            $occurrence = EventOccurrence::create([
                'event_id' => $event->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'max_attendees' => $data['max_attendees'] ?? null,
                'current_attendees' => 0,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            return $occurrence;
        });
    }
}
