<?php

declare(strict_types=1);

namespace App\Enums;

enum WashTicketStatus: string
{
    case ARRIVE = 'arrive';
    case ENREGISTRE = 'enregistre';
    case EN_ATTENTE = 'en_attente';
    case EN_LAVAGE = 'en_lavage';
    case LAVE = 'lave';
    case PRET = 'pret';
    case LIVRE = 'livre';
    case ANNULE = 'annule';

    /** Retourne le libellé humain du statut. */
    public function label(): string
    {
        return match ($this) {
            self::ARRIVE => 'Arrivé',
            self::ENREGISTRE => 'Enregistré',
            self::EN_ATTENTE => 'En attente',
            self::EN_LAVAGE => 'En lavage',
            self::LAVE => 'Lavé',
            self::PRET => 'Prêt à sortir',
            self::LIVRE => 'Livré',
            self::ANNULE => 'Annulé',
        };
    }
}
