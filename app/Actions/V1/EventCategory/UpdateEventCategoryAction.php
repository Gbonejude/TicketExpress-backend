<?php

declare(strict_types=1);

namespace App\Actions\V1\EventCategory;

use App\Actions\Contracts\Action;
use App\Models\EventCategory;

final class UpdateEventCategoryAction implements Action
{
    /**
     * @param  array{
     *     eventCategory: EventCategory,
     *     name?: string,
     *     slug?: string,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $eventCategory = $data['eventCategory'];

        $eventCategory->update(collect($data)->except(['eventCategory'])->toArray());

        return $eventCategory->fresh();
    }
}
