<?php

declare(strict_types=1);

namespace App\Actions\V1\TicketType;

use App\Actions\Contracts\Action;
use App\Models\TicketType;

final class DeleteTicketTypeAction implements Action
{
    /**
     * @param  array{
     *     ticketType: TicketType
     * }  $data
     */
    public function execute(array $data): bool
    {
        return $data['ticketType']->delete();
    }
}
