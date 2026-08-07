<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Event extends Model implements HasMedia
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organizer_id',
        'category_id',
        'venue_id',
        'title',
        'slug',
        'description',
        'start_date',
        'end_date',
        'max_attendees',
        'status',
        'event_type',
        'online_url',
        'refund_allowed',
        'refund_days_before',
        'checkin_open_hours_before',
        'checkin_close_hours_after',
    ];

    protected $with = [
        'media',
    ];

    /**
     * @return Attribute<string|null, never>
     */
    public function banner(): Attribute
    {
        $media = $this->media->first();

        return Attribute::make(
            get: fn () => $media instanceof Media
                ? $media->getUrl()
                : null,
        );
    }

    public function registerMediaCollections(?Media $media = null): void
    {
        $this->addMediaCollection(name: 'events')
            ->singleFile();

        $this->addMediaConversion(name: 'thumb')
            ->fit(
                fit: Fit::Crop,
                desiredWidth: 300,
                desiredHeight: 150,
            )
            ->sharpen(amount: 10);
    }

    /**
     * @return Attribute<string|null, never>
     */
    public function thumbnail(): Attribute
    {
        $media = $this->media->first();

        return Attribute::make(
            get: fn () => $media instanceof Media
                ? $media->getUrl(
                    conversionName: 'thumb',
                )
                : null,
        );
    }

    /**
     * @return BelongsTo<Organizer, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(related: Organizer::class);
    }

    /**
     * @return BelongsTo<EventCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(related: EventCategory::class, foreignKey: 'category_id');
    }

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(related: Venue::class);
    }

    /**
     * @return HasMany<TicketType, $this>
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(related: TicketType::class);
    }

    /**
     * @return HasMany<EventOccurrence, $this>
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(related: EventOccurrence::class);
    }

    /**
     * Les billets émis pour cet événement, à travers ses types de billets.
     *
     * Permet de compter les billets vendus et scannés en une requête
     * (`withCount`) au lieu d'un appel de statistiques par événement — ce dont a
     * besoin l'écran « Événements en cours ».
     *
     * @return HasManyThrough<Ticket, TicketType, $this>
     */
    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: Ticket::class,
            through: TicketType::class,
            firstKey: 'event_id',
            secondKey: 'ticket_type_id',
        );
    }

    /**
     * @return BelongsToMany<Coupon, $this>
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(related: Coupon::class, table: 'event_coupon');
    }

    /**
     * Users who favorited this event.
     *
     * @return BelongsToMany<User, $this>
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'event_favorites')
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => EventStatus::class,
            'event_type' => EventType::class,
            'max_attendees' => 'integer',
            'refund_allowed' => 'boolean',
            'refund_days_before' => 'integer',

            // `null` se distingue de `0` : le premier veut dire « hérite du
            // réglage plateforme », le second « n'ouvre pas une minute avant
            // l'heure ». Le cast primitif laisse null intact, c'est ce qu'on
            // veut ici.
            'checkin_open_hours_before' => 'float',
            'checkin_close_hours_after' => 'float',
        ];
    }
}
