<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case PHYSICAL = 'physical';
    case ONLINE = 'online';

    /**
     * Returns the label in French for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => 'Physique',
            self::ONLINE => 'En ligne',
        };
    }
}
