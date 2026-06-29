<?php

declare(strict_types=1);

namespace App\Actions\V1\TicketType;

use App\Actions\Contracts\Action;
use App\Models\TicketType;

final class CreateTicketTypeAction implements Action
{
    /**
     * @param  array{
     *     event_id: string,
     *     name: string,
     *     description?: string|null,
     *     price: string,
     *     quantity: int,
     *     sold_quantity?: int,
     *     sale_start_date?: string|null,
     *     sale_end_date?: string|null,
     *     benefits?: array<int, string>|null,
     *     location_details?: string|null,
     *     is_featured?: bool|null,
     *     sort_order?: int|null,
     *     promotional_price?: string|null,
     *     promotion_start_date?: string|null,
     *     promotion_end_date?: string|null
     * }  $data
     */
    public function execute(array $data): TicketType
    {
        return TicketType::create($data);
    }
}
