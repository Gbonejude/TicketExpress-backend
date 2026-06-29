<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'ticket_type_id',
        'attendee_name',
        'attendee_email',
        'qr_code',
        'ticket_number',
        'status',
        'checked_in_at',
        'checked_in_by',
        'access_method',
        'online_access_link',
        'refund_reason',
        'refunded_at',
    ];

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(related: Order::class);
    }

    /**
     * @return BelongsTo<TicketType, $this>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(related: TicketType::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'checked_in_by');
    }

    /**
     * @return HasMany<CheckIn, $this>
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(related: CheckIn::class);
    }

    /**
     * Check if ticket has been checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Check if ticket allows online access
     */
    public function isOnlineAccess(): bool
    {
        return in_array($this->access_method, ['online', 'hybrid']);
    }

    /**
     * Check if ticket allows physical access
     */
    public function isPhysicalAccess(): bool
    {
        return in_array($this->access_method, ['physical', 'hybrid']);
    }

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'checked_in_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }
}
