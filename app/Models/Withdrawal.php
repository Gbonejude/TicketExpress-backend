<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Database\Factories\WithdrawalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read WithdrawalStatus $status
 */
final class Withdrawal extends Model
{
    /** @use HasFactory<WithdrawalFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organizer_id',
        'requester_phone',
        'amount',
        'status',
        'payment_method',
        'processed_at',
        'processed_by',
        'notes',
        'payout_reference',
    ];

    /**
     * @return BelongsTo<Organizer, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(related: Organizer::class);
    }

    /**
     * L'agent qui a approuvé, rejeté ou payé la demande.
     *
     * @return BelongsTo<User, $this>
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'processed_by');
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => WithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}
