<?php

declare(strict_types=1);

namespace App\Actions\V1\Organizer;

use App\Actions\Contracts\Action;
use App\Events\Organizer\OrganizerCreatedEvent;
use App\Models\Organizer;
use Illuminate\Http\UploadedFile;

final class CreateOrganizerAction implements Action
{
    /**
     * @param  array{
     *     user_id: string,
     *     company_name: string,
     *     description?: string|null,
     *     website?: string|null,
     *     status?: string,
     *     logo?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $organizer = Organizer::create(collect($data)->except(['logo'])->toArray());

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            $organizer->addMedia($data['logo'])
                ->toMediaCollection(collectionName: 'organizers');
        }

        // Dispatch OrganizerCreatedEvent
        event(new OrganizerCreatedEvent($organizer));

        return $organizer;
    }
}
