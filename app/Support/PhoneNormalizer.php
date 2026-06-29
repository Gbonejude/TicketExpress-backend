<?php

declare(strict_types=1);

namespace App\Support;

final class PhoneNormalizer
{
    private const DEFAULT_COUNTRY = '228'; // Togo

    public static function toE164(string $phone, string $defaultCountry = self::DEFAULT_COUNTRY): string
    {
        $cleaned = preg_replace('/[\s\-\.\(\)]+/', '', $phone) ?? $phone;
        $cleaned = preg_replace('/^(\+|00)/', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/[^0-9]/', '', $cleaned) ?? $cleaned;
        if ($cleaned === '') {
            return '';
        }
        if (! str_starts_with($cleaned, $defaultCountry) && mb_strlen($cleaned) <= 9) {
            $cleaned = $defaultCountry.mb_ltrim($cleaned, '0');
        }

        return $cleaned;
    }

    public static function pretty(string $phone, string $defaultCountry = self::DEFAULT_COUNTRY): string
    {
        $canonical = self::toE164($phone, $defaultCountry);
        if ($canonical === '') {
            return '';
        }
        if (str_starts_with($canonical, $defaultCountry)) {
            $local = mb_substr($canonical, mb_strlen($defaultCountry));

            return '+'.$defaultCountry.' '.mb_trim(chunk_split($local, 2, ' '));
        }

        return '+'.$canonical;
    }
}
