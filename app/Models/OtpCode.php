<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $phone
 * @property string $code
 * @property Carbon $expires_at
 * @property int $attempts
 * @property Carbon|null $verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class OtpCode extends Model
{
    use HasUlids;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'attempts',
        'code',
        'expires_at',
        'phone',
        'verified_at',
    ];

    /** Valid OTPs: not expired, not verified, not blocked.
     */
    /** @param  Builder<OtpCode>  $query */
    public function scopeValid(Builder $query): void
    {
        $query->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', 5);
    }

    /** Whether a new OTP send is still within the 60-second cooldown.
     *
     */
    public function isOnCooldown(): bool
    {
        return $this->created_at->diffInSeconds(now()) < 60;
    }

    /** Whether the OTP is blocked due to too many failed attempts.
     *
     */
    public function isBlocked(): bool
    {
        return $this->attempts >= 5;
    }

    /** Whether the OTP has passed its expiry time.
     *
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
