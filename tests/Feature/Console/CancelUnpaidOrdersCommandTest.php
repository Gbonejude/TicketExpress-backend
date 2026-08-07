<?php

declare(strict_types=1);

use App\Console\Commands\CancelUnpaidOrdersCommand;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Une commande impayée retient ses places : le stock est engagé dès la création,
 * sinon deux acheteurs se disputeraient le même siège pendant qu'ils règlent.
 * Passé le délai, elle est annulée et les places reviennent au stock.
 */
beforeEach(function (): void {
    $this->tier = TicketType::factory()->create(['quantity' => 100, 'sold_quantity' => 10]);
});

/**
 * Une commande en attente, datée, qui a réservé `$quantity` places.
 */
function unpaidOrder(TicketType $tier, int $minutesAgo, int $quantity = 2): Order
{
    $placedAt = now()->subMinutes($minutesAgo);

    /** @var Order $order */
    $order = Order::factory()->pending()->create([
        'created_at' => $placedAt,
        'updated_at' => $placedAt,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'ticket_type_id' => $tier->id,
        'quantity' => $quantity,
        'unit_price' => $tier->price,
        'subtotal' => $tier->price * $quantity,
    ]);

    $tier->increment('sold_quantity', $quantity);

    return $order;
}

it('cancels an order left unpaid past the delay and gives the seats back', function (): void {
    $order = unpaidOrder($this->tier, minutesAgo: CancelUnpaidOrdersCommand::GRACE_MINUTES + 1);

    expect($this->tier->fresh()->sold_quantity)->toBe(12);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED)
        ->and($order->fresh()->cancelled_at)->not->toBeNull()
        ->and($this->tier->fresh()->sold_quantity)->toBe(10);
});

it('leaves an order that is still within the delay alone', function (): void {
    $order = unpaidOrder($this->tier, minutesAgo: CancelUnpaidOrdersCommand::GRACE_MINUTES - 2);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::PENDING)
        ->and($this->tier->fresh()->sold_quantity)->toBe(12);
});

it('never touches a paid order, however old', function (): void {
    $order = unpaidOrder($this->tier, minutesAgo: 500);
    $order->update(['status' => OrderStatus::PAID, 'paid_at' => now()->subMinutes(490)]);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::PAID)
        ->and($this->tier->fresh()->sold_quantity)->toBe(12);
});

it('spares an order whose payment attempt is recent', function (): void {
    // Un paiement mobile money peut revenir plusieurs minutes après avoir été
    // lancé. Annuler pendant ce temps rendrait les places au stock, puis le
    // retour de l'opérateur repasserait la commande à « payée » : on aurait vendu
    // deux fois le même siège. Chaque tentative rouvre donc un délai plein.
    $order = unpaidOrder($this->tier, minutesAgo: CancelUnpaidOrdersCommand::GRACE_MINUTES + 30);

    Payment::factory()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::NON_PAYE,
        'created_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::PENDING)
        ->and($this->tier->fresh()->sold_quantity)->toBe(12);
});

it('cancels an order whose payment attempt is itself stale', function (): void {
    $order = unpaidOrder($this->tier, minutesAgo: CancelUnpaidOrdersCommand::GRACE_MINUTES + 30);

    Payment::factory()->create([
        'order_id' => $order->id,
        'amount' => $order->total_amount,
        'status' => PaymentStatus::NON_PAYE,
        'created_at' => now()->subMinutes(CancelUnpaidOrdersCommand::GRACE_MINUTES + 20),
        'updated_at' => now()->subMinutes(CancelUnpaidOrdersCommand::GRACE_MINUTES + 20),
    ]);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED)
        ->and($this->tier->fresh()->sold_quantity)->toBe(10);
});

it('changes nothing on a dry run', function (): void {
    $order = unpaidOrder($this->tier, minutesAgo: CancelUnpaidOrdersCommand::GRACE_MINUTES + 1);

    $this->artisan('orders:cancel-unpaid', ['--dry-run' => true])->assertSuccessful();

    expect($order->fresh()->status)->toBe(OrderStatus::PENDING)
        ->and($this->tier->fresh()->sold_quantity)->toBe(12);
});

it('frees the seats of several stale orders in one pass', function (): void {
    unpaidOrder($this->tier, minutesAgo: 20, quantity: 3);
    unpaidOrder($this->tier, minutesAgo: 40, quantity: 1);

    expect($this->tier->fresh()->sold_quantity)->toBe(14);

    $this->artisan('orders:cancel-unpaid')->assertSuccessful();

    expect($this->tier->fresh()->sold_quantity)->toBe(10)
        ->and(Order::where('status', OrderStatus::PENDING)->count())->toBe(0);
});
