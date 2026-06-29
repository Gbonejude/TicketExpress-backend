<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EventOccurrenceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EventOccurrence extends Model
{
    /** @use HasFactory<EventOccurrenceFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'start_date',
        'end_date',
        'max_attendees',
        'current_attendees',
        'status',
        'notes',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(related: Event::class);
    }

    /**
     * @return HasMany<TicketType, $this>
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(related: TicketType::class, foreignKey: 'occurrence_id');
    }

    /**
     * Calculate availability percentage based on attendees
     */
    public function availabilityPercentage(): float
    {
        if ($this->max_attendees === null || $this->max_attendees === 0) {
            return 100;
        }

        return round((($this->max_attendees - $this->current_attendees) / $this->max_attendees) * 100, 2);
    }

    /**
     * Get remaining capacity
     */
    public function remainingCapacity(): ?int
    {
        if ($this->max_attendees === null) {
            return null;
        }

        return max(0, $this->max_attendees - $this->current_attendees);
    }

    /**
     * Check if occurrence is sold out
     */
    public function isSoldOut(): bool
    {
        if ($this->status === 'sold_out') {
            return true;
        }

        if ($this->max_attendees === null) {
            return false;
        }

        return $this->current_attendees >= $this->max_attendees;
    }

    /**
     * Check if occurrence is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'max_attendees' => 'integer',
            'current_attendees' => 'integer',
        ];
    }
}
