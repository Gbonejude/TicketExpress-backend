<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Masque les coordonnées personnelles avant qu'elles n'atteignent les journaux.
 *
 * Un log se copie, s'archive et se partage pour déboguer : il ne doit pas
 * devenir un annuaire des clients. On garde juste assez pour reconnaître une
 * ligne (première lettre, domaine, quatre derniers chiffres).
 */
final class LogMask
{
    public static function email(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return $email;
        }

        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 1).'***@'.$domain;
    }

    public static function phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return str_repeat('*', max(strlen($digits) - 4, 0)).substr($digits, -4);
    }
}
