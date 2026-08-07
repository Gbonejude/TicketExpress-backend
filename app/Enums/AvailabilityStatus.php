<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Ticket availability status based on stock levels
 */
enum AvailabilityStatus: string
{
    case AVAILABLE = 'available';           // Disponible (< 70% sold)
    case HIGH_DEMAND = 'high_demand';       // Forte demande (70-84% sold)
    case RUNNING_OUT = 'running_out';       // En voie d'épuisement (85-94% sold)
    case ALMOST_SOLD_OUT = 'almost_sold_out'; // Bientôt épuisé (95%+ sold)
    case LIMITED = 'limited';               // Places limitées (< 50 remaining)
    case SOLD_OUT = 'sold_out';            // Complet (0 remaining)

    /**
     * Hors de la fenêtre de vente — indépendamment du stock.
     *
     * `sale_start_date` et `sale_end_date` étaient enregistrées et jamais
     * appliquées : un événement terminé continuait d'annoncer ses tarifs comme
     * achetables, et la caisse les acceptait. Deux états et non un seul, parce
     * que « ce n'est pas encore ouvert » et « c'est fini » n'appellent pas la
     * même réaction du visiteur : le premier vaut un rappel, le second non.
     */
    case SALE_NOT_STARTED = 'sale_not_started'; // Bientôt en vente
    case SALE_CLOSED = 'sale_closed';           // Vente fermée

    /**
     * Get human-readable label in French
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::HIGH_DEMAND => 'Forte demande',
            self::RUNNING_OUT => 'En voie d\'épuisement',
            self::ALMOST_SOLD_OUT => 'Bientôt épuisé',
            self::LIMITED => 'Places limitées',
            self::SOLD_OUT => 'Complet',
            self::SALE_NOT_STARTED => 'Bientôt en vente',
            self::SALE_CLOSED => 'Vente fermée',
        };
    }

    /**
     * Get color for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'green',
            self::HIGH_DEMAND => 'orange',
            self::RUNNING_OUT => 'red',
            self::ALMOST_SOLD_OUT => 'red',
            self::LIMITED => 'yellow',
            self::SOLD_OUT => 'gray',
            self::SALE_NOT_STARTED => 'blue',
            self::SALE_CLOSED => 'gray',
        };
    }

    /**
     * Get emoji icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::AVAILABLE => '✅',
            self::HIGH_DEMAND => '🔥',
            self::RUNNING_OUT => '⚠️',
            self::ALMOST_SOLD_OUT => '🚨',
            self::LIMITED => '⏰',
            self::SOLD_OUT => '❌',
            self::SALE_NOT_STARTED => '🗓️',
            self::SALE_CLOSED => '🔒',
        };
    }

    /**
     * Get CSS class for badges
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'badge-success',
            self::HIGH_DEMAND => 'badge-warning',
            self::RUNNING_OUT => 'badge-danger',
            self::ALMOST_SOLD_OUT => 'badge-danger',
            self::LIMITED => 'badge-info',
            self::SOLD_OUT => 'badge-secondary',
            self::SALE_NOT_STARTED => 'badge-info',
            self::SALE_CLOSED => 'badge-secondary',
        };
    }

    /**
     * Check if tickets can still be purchased
     *
     * Une liste explicite plutôt qu'une exclusion : chaque état ajouté doit être
     * rangé d'un côté ou de l'autre. Avec `!== SOLD_OUT`, tout nouvel état
     * devenait achetable par défaut — ce qui est exactement l'erreur à ne pas
     * refaire, puisqu'elle porte sur le droit d'encaisser de l'argent.
     */
    public function isAvailableForPurchase(): bool
    {
        return match ($this) {
            self::SOLD_OUT, self::SALE_NOT_STARTED, self::SALE_CLOSED => false,
            self::AVAILABLE, self::HIGH_DEMAND, self::RUNNING_OUT,
            self::ALMOST_SOLD_OUT, self::LIMITED => true,
        };
    }

    /**
     * Get urgency level (0-5, 5 being most urgent)
     */
    public function urgencyLevel(): int
    {
        return match ($this) {
            self::AVAILABLE => 0,
            self::HIGH_DEMAND => 2,
            self::RUNNING_OUT => 3,
            self::LIMITED => 4,
            self::ALMOST_SOLD_OUT => 5,
            self::SOLD_OUT => 0, // Not urgent since can't buy
            self::SALE_NOT_STARTED => 0,
            self::SALE_CLOSED => 0,
        };
    }
}
