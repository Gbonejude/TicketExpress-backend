<?php

declare(strict_types=1);

namespace App\Actions\V1\EventCategory;

use App\Actions\Contracts\Action;
use App\Models\EventCategory;

final class DeleteEventCategoryAction implements Action
{
    /**
     * @param  array{eventCategory: EventCategory}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['eventCategory']->delete();
    }
}
