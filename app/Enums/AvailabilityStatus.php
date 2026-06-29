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
        };
    }

    /**
     * Check if tickets can still be purchased
     */
    public function isAvailableForPurchase(): bool
    {
        return $this !== self::SOLD_OUT;
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
        };
    }
}
