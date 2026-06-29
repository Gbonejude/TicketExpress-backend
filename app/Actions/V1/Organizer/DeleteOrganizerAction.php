<?php

declare(strict_types=1);

namespace App\Actions\V1\Organizer;

use App\Actions\Contracts\Action;
use App\Models\Organizer;

final class DeleteOrganizerAction implements Action
{
    /**
     * @param  array{organizer: Organizer}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['organizer']->delete();
    }
}
