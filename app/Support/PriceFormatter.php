<?php

declare(strict_types=1);

namespace App\Support;

final class PriceFormatter
{
    private const DEFAULT_DECIMAL_SEPARATOR = ',';

    private const DEFAULT_THOUSANDS_SEPARATOR = ' ';

    public static function format(int|float|string|null $amount, string $currency = 'XOF', int $decimals = 0): string
    {
        $value = (float) ($amount ?? 0);

        return number_format($value, $decimals, self::DEFAULT_DECIMAL_SEPARATOR, self::DEFAULT_THOUSANDS_SEPARATOR).' '.$currency;
    }

    public static function number(int|float|string|null $amount, int $decimals = 0): string
    {
        $value = (float) ($amount ?? 0);

        return number_format($value, $decimals, self::DEFAULT_DECIMAL_SEPARATOR, self::DEFAULT_THOUSANDS_SEPARATOR);
    }
}
