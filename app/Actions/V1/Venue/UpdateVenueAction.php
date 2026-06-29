<?php

declare(strict_types=1);

namespace App\Actions\V1\Venue;

use App\Actions\Contracts\Action;
use App\Models\Venue;

final class UpdateVenueAction implements Action
{
    /**
     * @param  array{
     *     model: Venue,
     *     name?: string,
     *     address?: string,
     *     city?: string,
     *     country?: string,
     *     capacity?: int|null,
     *     latitude?: string|null,
     *     longitude?: string|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $venue = $data['model'];
        $venue->update(collect($data)->except(['model'])->toArray());

        return $venue->fresh();
    }
}
