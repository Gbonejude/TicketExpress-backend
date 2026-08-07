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

    /*
    |--------------------------------------------------------------------------
    | Check-in window
    |--------------------------------------------------------------------------
    |
    | Marges, en heures, autour d'un événement pendant lesquelles ses billets
    | peuvent être validés : le portique ouvre `open_hours_before` avant le
    | début et ferme `close_hours_after` après la fin. Hors de cette fenêtre,
    | l'entrée est refusée — sans quoi un billet de demain se consomme
    | aujourd'hui, et son porteur se fait refouler le jour dit.
    |
    | Ces valeurs ne sont que le point de départ : le super-admin les modifie
    | depuis les réglages (table settings), voir App\Support\CheckInWindow.
    |
    */
    'checkin' => [
        'open_hours_before' => (float) env('TICKETEXPRESS_CHECKIN_OPEN_HOURS_BEFORE', 4),
        'close_hours_after' => (float) env('TICKETEXPRESS_CHECKIN_CLOSE_HOURS_AFTER', 4),
    ],
];
