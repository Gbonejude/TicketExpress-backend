<?php

declare(strict_types=1);

namespace App\Actions\V1\EventCategory;

use App\Actions\Contracts\Action;
use App\Models\EventCategory;

final class CreateEventCategoryAction implements Action
{
    /**
     * @param  array{
     *     name: string,
     *     slug: string,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $eventCategory = EventCategory::create(collect($data)->toArray());

        return $eventCategory;
    }
}
