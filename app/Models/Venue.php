<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'capacity',
        'latitude',
        'longitude',
    ];

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(related: Event::class);
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'capacity' => 'integer',
        ];
    }
}
