<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Single source of truth for the TicketExpress platform commission (5% by
 * default) taken on each ticket sold. The organizer keeps the rest.
 *
 * The rate is editable from the back-office (persisted in the settings table);
 * the config value is only the initial fallback.
 */
final class Commission
{
    /**
     * Commission rate as a fraction (e.g. 0.05 for 5%).
     */
    public static function rate(): float
    {
        return (float) Setting::get(
            'commission_rate',
            config('ticketexpress.commission_rate', 0.05),
        );
    }

    /**
     * Platform commission on a gross amount.
     */
    public static function amountFor(float|int $gross): float
    {
        return round((float) $gross * self::rate(), 2);
    }

    /**
     * Amount the organizer nets from a gross amount (gross - commission).
     */
    public static function netFor(float|int $gross): float
    {
        return round((float) $gross - self::amountFor($gross), 2);
    }
}
