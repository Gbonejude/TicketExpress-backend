<?php

declare(strict_types=1);

namespace App\Actions\V1\Event;

use App\Actions\Contracts\Action;
use App\Models\Event;

final class DeleteEventAction implements Action
{
    /**
     * @param  array{event: Event}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['event']->delete();
    }
}
