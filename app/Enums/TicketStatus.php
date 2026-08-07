<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case VALID = 'valid';
    case USED = 'used';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Le portique de l'événement a fermé sans que le billet soit présenté.
     *
     * Distinct d'`ANNULÉ` (décision de la plateforme ou de l'organisateur) et de
     * `REMBOURSÉ` (l'argent est reparti) : personne n'a rien décidé, la date est
     * simplement passée. La nuance compte pour les rapports — un billet expiré
     * est une place vendue et non consommée, pas une vente défaite.
     */
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::VALID => 'Valide',
            self::USED => 'Utilisé',
            self::CANCELLED => 'Annulé',
            self::REFUNDED => 'Remboursé',
            self::EXPIRED => 'Expiré',
        };
    }
}
