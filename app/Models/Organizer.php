<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrganizerStatus;
use App\Enums\WithdrawalStatus;
use App\Support\Commission;
use Database\Factories\OrganizerFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'is_active',
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

    /**
     * Gross ticket revenue from this organizer's events (paid orders only).
     */
    public function grossRevenue(): float
    {
        return (float) OrderItem::query()
            ->whereHas('order', fn (Builder $q) => $q->where('status', OrderStatus::PAID->value))
            ->whereHas('ticketType.event', fn (Builder $q) => $q->where('organizer_id', $this->id))
            ->sum('subtotal');
    }

    /**
     * Platform commission withheld on this organizer's gross revenue.
     */
    public function platformCommission(): float
    {
        return Commission::amountFor($this->grossRevenue());
    }

    /**
     * Net revenue owed to the organizer (gross minus platform commission).
     */
    public function netRevenue(): float
    {
        return Commission::netFor($this->grossRevenue());
    }

    /**
     * Amount already paid out (approved + paid withdrawals).
     */
    public function totalWithdrawn(): float
    {
        return (float) $this->withdrawals()
            ->whereIn('status', [WithdrawalStatus::APPROVED->value, WithdrawalStatus::PAID->value])
            ->sum('amount');
    }

    /**
     * Montant demandé et encore en attente de décision.
     */
    public function pendingWithdrawn(): float
    {
        return (float) $this->withdrawals()
            ->where('status', WithdrawalStatus::PENDING->value)
            ->sum('amount');
    }

    /**
     * Ce qui reste réellement demandable.
     *
     * Les demandes en attente sont déduites, elles aussi : elles n'ont pas
     * encore été payées, mais elles sont engagées. Sans cette déduction, un
     * organisateur pouvait déposer autant de demandes qu'il voulait pour le même
     * solde, et la somme des retraits dépassait ses recettes.
     */
    public function availableBalance(): float
    {
        return round(
            $this->netRevenue() - $this->totalWithdrawn() - $this->pendingWithdrawn(),
            2,
        );
    }

    protected function casts(): array
    {
        return [
            'status' => OrganizerStatus::class,
            'is_active' => 'boolean',
        ];
    }
}
