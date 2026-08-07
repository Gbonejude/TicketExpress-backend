<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Database\Factories\TicketTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TicketType extends Model
{
    /** @use HasFactory<TicketTypeFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'occurrence_id',
        'name',
        'description',
        'price',
        'quantity',
        'sold_quantity',
        'min_purchase',
        'max_purchase',
        'sale_start_date',
        'sale_end_date',
        'promotional_price',
        'promotion_start_date',
        'promotion_end_date',
        'benefits',
        'location_details',
        'is_featured',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(related: Event::class);
    }

    /**
     * @return BelongsTo<EventOccurrence, $this>
     */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(related: EventOccurrence::class, foreignKey: 'occurrence_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(related: OrderItem::class);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(related: Ticket::class);
    }

    /**
     * Whether the sale window is open right now.
     *
     * `null` de chaque côté vaut « pas de borne » : un tarif sans date de fin
     * reste en vente, comme avant l'introduction de cette vérification.
     */
    public function isOnSale(): bool
    {
        $now = now();

        if ($this->sale_start_date !== null && $now->lt($this->sale_start_date)) {
            return false;
        }

        return $this->sale_end_date === null || $now->lte($this->sale_end_date);
    }

    /**
     * Get the availability status based on the sale window, sold quantity and
     * remaining tickets.
     *
     * Priority: sale window > SOLD_OUT > percentage-based (if >= 70%) > LIMITED >
     * percentage-based (if < 70%) > AVAILABLE
     *
     * La fenêtre passe **avant** le stock : `sale_start_date` et `sale_end_date`
     * étaient écrites et jamais lues, si bien qu'un événement terminé affichait
     * encore ses tarifs comme achetables. Et « Vente fermée » est plus juste que
     * « Complet » sur un concert d'il y a six mois : il restait des places, la
     * billetterie a simplement fermé.
     */
    public function availabilityStatus(): AvailabilityStatus
    {
        if ($this->sale_start_date !== null && now()->lt($this->sale_start_date)) {
            return AvailabilityStatus::SALE_NOT_STARTED;
        }

        if (! $this->isOnSale()) {
            return AvailabilityStatus::SALE_CLOSED;
        }

        $remaining = $this->remainingTickets();
        $soldPercentage = $this->soldPercentage();

        // SOLD_OUT takes absolute priority
        if ($remaining === 0 || $soldPercentage >= 100.0) {
            return AvailabilityStatus::SOLD_OUT;
        }

        // High percentage (>=70%) takes priority over LIMITED
        if ($soldPercentage >= 95.0) {
            return AvailabilityStatus::ALMOST_SOLD_OUT;
        }

        if ($soldPercentage >= 85.0) {
            return AvailabilityStatus::RUNNING_OUT;
        }

        if ($soldPercentage >= 70.0) {
            return AvailabilityStatus::HIGH_DEMAND;
        }

        // LIMITED applies when < 50 remaining AND sold% < 70%
        if ($remaining < 50) {
            return AvailabilityStatus::LIMITED;
        }

        // Default: AVAILABLE
        return AvailabilityStatus::AVAILABLE;
    }

    /**
     * Get the availability status (Laravel accessor).
     */
    public function getAvailabilityStatusAttribute(): AvailabilityStatus
    {
        return $this->availabilityStatus();
    }

    /**
     * Get remaining tickets count.
     */
    public function remainingTickets(): int
    {
        return max(0, $this->quantity - $this->sold_quantity);
    }

    /**
     * Get sold percentage.
     */
    public function soldPercentage(): float
    {
        if ($this->quantity === 0) {
            return 0.0;
        }

        return round(($this->sold_quantity / $this->quantity) * 100, 2);
    }

    /**
     * Get availability percentage.
     */
    public function availabilityPercentage(): float
    {
        if ($this->quantity === 0) {
            return 0.0;
        }

        return round((($this->quantity - $this->sold_quantity) / $this->quantity) * 100, 2);
    }

    /**
     * Alias for availabilityPercentage() for backward compatibility.
     */
    public function availablePercentage(): float
    {
        return $this->availabilityPercentage();
    }

    /**
     * Check if ticket type is sold out.
     */
    public function isSoldOut(): bool
    {
        return $this->sold_quantity >= $this->quantity;
    }

    /**
     * Check if ticket type is available for purchase.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->isSoldOut()) {
            return false;
        }

        if ($this->sale_start_date && $now->lt($this->sale_start_date)) {
            return false;
        }

        if ($this->sale_end_date && $now->gt($this->sale_end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a promotion is currently active.
     */
    public function hasActivePromotion(): bool
    {
        if ($this->promotional_price === null) {
            return false;
        }

        $now = now();

        if ($this->promotion_start_date && $now->lt($this->promotion_start_date)) {
            return false;
        }

        if ($this->promotion_end_date && $now->gt($this->promotion_end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Get the current effective price (promotional or regular).
     */
    public function currentPrice(): float
    {
        $price = $this->hasActivePromotion() && $this->promotional_price !== null
            ? $this->promotional_price
            : $this->price;

        return (float) $price;
    }

    /**
     * Calculate discount percentage for active promotions.
     */
    public function discountPercentage(): ?float
    {
        if (! $this->hasActivePromotion() || $this->promotional_price === null) {
            return null;
        }

        // Handle zero price edge case
        if ($this->price == 0) {
            return null;
        }

        return round((($this->price - $this->promotional_price) / $this->price) * 100, 0);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'quantity' => 'integer',
            'sold_quantity' => 'integer',
            'min_purchase' => 'integer',
            'max_purchase' => 'integer',
            'sale_start_date' => 'datetime',
            'sale_end_date' => 'datetime',
            'promotion_start_date' => 'datetime',
            'promotion_end_date' => 'datetime',
            'benefits' => 'array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
