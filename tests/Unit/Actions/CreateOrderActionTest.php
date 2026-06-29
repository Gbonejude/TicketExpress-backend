<?php

declare(strict_types=1);

use App\Actions\V1\Order\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = app(CreateOrderAction::class);
});

it('creates order with correct total amount', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+22890123456',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 3,
            ],
        ],
    ];

    $order = $this->action->execute($data);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->total_amount)->toBe('30000.00'); // 3 x 10,000
    expect($order->status)->toBe(OrderStatus::PENDING);
});

it('increments sold_quantity atomically', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 10,
        'price' => 10000,
    ]);

    $data = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'phone' => '+22890123457',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 5,
            ],
        ],
    ];

    $this->action->execute($data);

    $ticketType->refresh();
    expect($ticketType->sold_quantity)->toBe(15); // 10 + 5
});

it('throws exception when stock insufficient', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 98,
        'price' => 10000,
    ]);

    $data = [
        'first_name' => 'Bob',
        'last_name' => 'Johnson',
        'email' => 'bob@example.com',
        'phone' => '+22890123458',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 5, // Only 2 available
            ],
        ],
    ];

    expect(fn () => $this->action->execute($data))
        ->toThrow(DomainException::class, 'Stock insuffisant');
});

it('applies percent coupon correctly', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'TEST25',
        'type' => 'percent',
        'value' => 25,
        'max_usage' => 100,
        'used_count' => 0,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $data = [
        'first_name' => 'Alice',
        'last_name' => 'Brown',
        'email' => 'alice@example.com',
        'phone' => '+22890123459',
        'delivery_method' => 'email',
        'coupon_code' => 'TEST25',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 4, // 4 x 10,000 = 40,000
            ],
        ],
    ];

    $order = $this->action->execute($data);

    expect($order->total_amount)->toBe('30000.00'); // 25% off = 30,000

    $coupon->refresh();
    expect($coupon->used_count)->toBe(1);
});

it('applies fixed coupon correctly', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 20000,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'FIXED10K',
        'type' => 'fixed',
        'value' => 10000,
        'max_usage' => 100,
        'used_count' => 0,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $data = [
        'first_name' => 'Charlie',
        'last_name' => 'Davis',
        'email' => 'charlie@example.com',
        'phone' => '+22890123460',
        'delivery_method' => 'email',
        'coupon_code' => 'FIXED10K',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2, // 2 x 20,000 = 40,000
            ],
        ],
    ];

    $order = $this->action->execute($data);

    expect($order->total_amount)->toBe('30000.00'); // 40,000 - 10,000
});

it('rolls back transaction on error', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 99,
        'price' => 10000,
    ]);

    $initialCount = Order::count();
    $initialSold = $ticketType->sold_quantity;

    $data = [
        'first_name' => 'Dave',
        'last_name' => 'Wilson',
        'email' => 'dave@example.com',
        'phone' => '+22890123461',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 5, // Will fail
            ],
        ],
    ];

    try {
        $this->action->execute($data);
    } catch (DomainException $e) {
        // Expected
    }

    $ticketType->refresh();
    expect(Order::count())->toBe($initialCount); // No order created
    expect($ticketType->sold_quantity)->toBe($initialSold); // Stock unchanged
});

it('creates order items with captured unit price', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 15000,
    ]);

    $data = [
        'first_name' => 'Eve',
        'last_name' => 'Martinez',
        'email' => 'eve@example.com',
        'phone' => '+22890123462',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
            ],
        ],
    ];

    $order = $this->action->execute($data);

    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->unit_price)->toBe('15000.00');
    expect($order->items->first()->subtotal)->toBe('30000.00');
});

it('handles guest orders with null user_id', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $data = [
        // No user_id provided
        'first_name' => 'Guest',
        'last_name' => 'User',
        'email' => 'guest@example.com',
        'phone' => '+22890123463',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ],
        ],
    ];

    $order = $this->action->execute($data);

    expect($order->user_id)->toBeNull();
    expect($order->email)->toBe('guest@example.com');
});
