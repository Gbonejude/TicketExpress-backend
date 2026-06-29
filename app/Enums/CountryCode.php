<?php

declare(strict_types=1);

namespace App\Enums;

enum CountryCode: string
{
    case TG = 'TG'; // Togo
    case BJ = 'BJ'; // Bénin
    case GH = 'GH'; // Ghana
    case CI = 'CI'; // Côte d'Ivoire
    case SN = 'SN'; // Sénégal
    case ML = 'ML'; // Mali
    case BF = 'BF'; // Burkina Faso
    case NE = 'NE'; // Niger
    case NG = 'NG'; // Nigeria

    /** Retourne le nom complet du pays. */
    public function label(): string
    {
        return match ($this) {
            self::TG => 'Togo',
            self::BJ => 'Bénin',
            self::GH => 'Ghana',
            self::CI => "Côte d'Ivoire",
            self::SN => 'Sénégal',
            self::ML => 'Mali',
            self::BF => 'Burkina Faso',
            self::NE => 'Niger',
            self::NG => 'Nigeria',
        };
    }
}
