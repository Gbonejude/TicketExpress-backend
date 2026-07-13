<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class TicketDownloadLink extends Model
{
    use HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'order_id',
        'expires_at',
        'download_count',
        'max_downloads',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (TicketDownloadLink $link): void {
            if (empty($link->token)) {
                $link->token = self::generateUniqueToken();
            }
        });
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Generate a unique secure token.
     */
    private static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if link is still valid.
     */
    public function isValid(): bool
    {
        return $this->expires_at > now()
            && $this->download_count < $this->max_downloads;
    }

    /**
     * Check if link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Check if download limit reached.
     */
    public function isLimitReached(): bool
    {
        return $this->download_count >= $this->max_downloads;
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'download_count' => 'integer',
            'max_downloads' => 'integer',
        ];
    }
}
