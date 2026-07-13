<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case STRIPE = 'stripe';
    case WAVE = 'wave';
    case FLOOZ = 'flooz';
    case TMONEY = 'tmoney';
    case PAYPAL = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
            self::WAVE => 'Wave',
            self::FLOOZ => 'Flooz',
            self::TMONEY => 'Mix by Yas',
            self::PAYPAL => 'PayPal',
        };
    }
}
