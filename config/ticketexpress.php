<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Platform commission
    |--------------------------------------------------------------------------
    |
    | The share TicketExpress keeps on each ticket sold. The organizer receives
    | the remainder (1 - commission_rate). Expressed as a fraction (0.05 = 5%).
    |
    */
    'commission_rate' => (float) env('TICKETEXPRESS_COMMISSION_RATE', 0.05),
];
