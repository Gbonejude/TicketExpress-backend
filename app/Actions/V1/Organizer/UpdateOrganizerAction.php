<?php

declare(strict_types=1);

namespace App\Actions\V1\Organizer;

use App\Actions\Contracts\Action;
use App\Events\Organizer\OrganizerStatusUpdatedEvent;
use App\Events\Organizer\OrganizerUpdatedEvent;
use App\Models\Organizer;
use Illuminate\Http\UploadedFile;

final class UpdateOrganizerAction implements Action
{
    /**
     * @param  array{
     *     organizer: Organizer,
     *     user_id?: string,
     *     company_name?: string,
     *     description?: string|null,
     *     website?: string|null,
     *     status?: string,
     *     rejection_reason?: string|null,
     *     logo?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $organizer = $data['organizer'];
        $oldStatus = $organizer->status;

        $organizer->update(collect($data)->except(['organizer', 'logo'])->toArray());

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            $organizer->addMedia($data['logo'])
                ->toMediaCollection(collectionName: 'organizers');
        }

        $organizer = $organizer->fresh();

        // Dispatch OrganizerUpdatedEvent
        event(new OrganizerUpdatedEvent($organizer));

        // Dispatch OrganizerStatusUpdatedEvent if status changed
        if (isset($data['status']) && $oldStatus !== $organizer->status) {
            event(new OrganizerStatusUpdatedEvent($organizer));
        }

        return $organizer;
    }
}
