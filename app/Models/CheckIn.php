<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CheckInFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CheckIn extends Model
{
    /** @use HasFactory<CheckInFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'scanned_by',
        'scanned_at',
        'device_info',
    ];

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(related: Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function scannedByUser(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'scanned_by');
    }

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }
}
