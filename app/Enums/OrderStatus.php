<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
        };
    }
}
