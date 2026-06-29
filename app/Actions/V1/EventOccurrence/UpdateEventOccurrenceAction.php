<?php

declare(strict_types=1);

namespace App\Actions\V1\EventOccurrence;

use App\Actions\Contracts\Action;
use App\Models\EventOccurrence;
use Illuminate\Support\Facades\DB;

final class UpdateEventOccurrenceAction implements Action
{
    /**
     * Update an event occurrence
     *
     * @param  array{occurrence_id: string, start_date?: string, end_date?: string, max_attendees?: int|null, status?: string, notes?: string|null}  $data
     */
    public function execute(array $data): EventOccurrence
    {
        return DB::transaction(function () use ($data) {
            $occurrence = EventOccurrence::findOrFail($data['occurrence_id']);

            $updateData = array_filter($data, fn ($key) => ! in_array($key, ['occurrence_id']), ARRAY_FILTER_USE_KEY);

            $occurrence->update(array_filter($updateData, fn ($value) => $value !== null || array_key_exists('max_attendees', $data)));

            return $occurrence->fresh();
        });
    }
}
