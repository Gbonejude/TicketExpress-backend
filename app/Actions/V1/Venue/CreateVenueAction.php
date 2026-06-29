<?php

declare(strict_types=1);

namespace App\Actions\V1\Venue;

use App\Actions\Contracts\Action;
use App\Models\Venue;

final class CreateVenueAction implements Action
{
    /**
     * @param  array{
     *     name: string,
     *     address: string,
     *     city: string,
     *     country?: string|null,
     *     capacity?: int|null,
     *     latitude?: string|null,
     *     longitude?: string|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        return Venue::create($data);
    }
}
