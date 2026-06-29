<?php

declare(strict_types=1);

namespace App\Actions\V1\EventOccurrence;

use App\Actions\Contracts\Action;
use App\Models\EventOccurrence;
use Illuminate\Support\Facades\DB;

final class DeleteEventOccurrenceAction implements Action
{
    /**
     * Delete an event occurrence
     * Note: Cascade deletes associated ticket types
     *
     * @param  array{occurrence_id: string}  $data
     */
    public function execute(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $occurrence = EventOccurrence::findOrFail($data['occurrence_id']);

            // Cascade will automatically delete related ticket_types
            return $occurrence->delete();
        });
    }
}
