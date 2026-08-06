<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasRoles;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasUlids;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'birthday',
        'gender',
        'email',
        'password',
        'phone',
        'address',
        'organization_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = [
        'media',
    ];

    /**
     * @return Attribute<string, never-return>
     */
    public function image(): Attribute
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
        $this->addMediaCollection(name: 'users')
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
     * Get the organizer associated with this user (if they are an organizer).
     *
     * @return HasOne<Organizer, $this>
     */
    public function organizer(): HasOne
    {
        return $this->hasOne(related: Organizer::class);
    }

    /**
     * Les commandes passées par ce compte.
     *
     * Ne couvre pas les achats en invité : ceux-là n'ont pas de `user_id`, par
     * construction. Sert aux agrégats de l'écran Participants (nombre de
     * commandes payées, total dépensé).
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(related: Order::class);
    }

    /**
     * Events this user has marked as favorite.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function favoriteEvents(): BelongsToMany
    {
        return $this->belongsToMany(related: Event::class, table: 'event_favorites')
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
