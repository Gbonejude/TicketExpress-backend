<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(related: Review::class);
    }

    /**
     * @return BelongsToMany<Coupon, $this>
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(related: Coupon::class, table: 'event_coupon');
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => EventStatus::class,
            'max_attendees' => 'integer',
        ];
    }
}
