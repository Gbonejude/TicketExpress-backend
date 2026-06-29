<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read OrderStatus $status
 * @property-read DeliveryMethod|null $delivery_method
 */
final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'total_amount',
        'status',
        'payment_method',
        'delivery_method',
        'order_number',
        'paid_at',
        'confirmed_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Order $order): void {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(related: User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(related: OrderItem::class);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(related: Ticket::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(related: Payment::class);
    }

    /**
     * @return HasOne<TicketDownloadLink, $this>
     */
    public function downloadLink(): HasOne
    {
        return $this->hasOne(related: TicketDownloadLink::class);
    }

    /**
     * Generate a unique 7-digit order number.
     */
    private static function generateOrderNumber(): string
    {
        do {
            $number = str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => OrderStatus::class,
            'delivery_method' => DeliveryMethod::class,
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }
}
