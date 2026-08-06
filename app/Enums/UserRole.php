<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case PARTICIPANT = 'participant';
    case ADMIN = 'admin';
    case ORGANIZER_MANAGER = 'organizer-manager';
    case SUPER_ADMIN = 'super-admin';

    /**
     * Returns the label in French for display
     */
    public function label(): string
    {
        return match ($this) {
            self::PARTICIPANT => 'Participant',
            self::ADMIN => 'Administrateur',
            self::ORGANIZER_MANAGER => 'Organisateur',
            self::SUPER_ADMIN => 'Super Administrateur',
        };
    }
}
