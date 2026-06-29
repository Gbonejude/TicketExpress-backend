<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizerStatus;
use Database\Factories\OrganizerFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string|null $logo
 * @property-read string|null $thumbnail
 * @property-read OrganizerStatus $status
 */
final class Organizer extends Model implements HasMedia
{
    /** @use HasFactory<OrganizerFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'description',
        'website',
        'status',
        'rejection_reason',
    ];

    protected $with = [
        'media',
    ];

    /**
     * @return Attribute<string, never-return>
     */
    public function logo(): Attribute
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
        $this->addMediaCollection(name: 'organizers')
            ->singleFile();

        $this->addMediaConversion(name: 'thumb')
            ->fit(
                fit: Fit::Crop,
                desiredWidth: 80,
                desiredHeight: 80,
            )
            ->sharpen(amount: 10);
    }

    /**
     * @return Attribute<string, never-return>
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(related: Event::class);
    }

    /**
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(related: Withdrawal::class);
    }

    protected function casts(): array
    {
        return [
            'status' => OrganizerStatus::class,
        ];
    }
}
