<?php

declare(strict_types=1);

namespace App\Actions\V1\Venue;

use App\Actions\Contracts\Action;
use App\Models\Venue;

final class DeleteVenueAction implements Action
{
    /**
     * @param  array{model: Venue}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['model']->delete();
    }
}
