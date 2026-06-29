<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
            self::CANCELLED => 'Annulé',
            self::FINISHED => 'Terminé',
        };
    }
}
