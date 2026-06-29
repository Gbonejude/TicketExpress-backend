<?php

declare(strict_types=1);

namespace App\Enums;

enum DeliveryMethod: string
{
    case EMAIL = 'email';
    case WHATSAPP = 'whatsapp';
    case BOTH = 'both';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::WHATSAPP => 'WhatsApp',
            self::BOTH => 'Email et WhatsApp',
        };
    }
}
