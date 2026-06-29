<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case NON_PAYE = 'non_paye';
    case PAYE = 'paye';

    /** Retourne le libellé humain du statut de paiement. */
    public function label(): string
    {
        return match ($this) {
            self::NON_PAYE => 'Non payé',
            self::PAYE => 'Payé',
        };
    }
}
