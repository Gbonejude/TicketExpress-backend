<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Don't seed categories - let factories create them as needed
});

it('allows guest to create order without authentication', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $payload = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+22890123456',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    $response->assertCreated()
        ->assertJson(['success' => true])
        ->assertJsonStructure([
            'data' => [
                'id',
                'userId',
                'firstName',
                'lastName',
                'email',
                'phone',
                'totalAmount',
                'status',
                'items',
            ],
        ]);

    expect(Order::where('email', 'john@example.com')->exists())->toBeTrue();
    expect(Order::first()->user_id)->toBeNull();
});

it('decrements ticket stock atomically when order is created', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 20,
        'price' => 10000,
    ]);

    $payload = [
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

    $this->postJson('/api/v1/orders', $payload)->assertCreated();

    $ticketType->refresh();
    expect($ticketType->sold_quantity)->toBe(25);
});

it('rejects order when insufficient stock available', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 95,
        'price' => 10000,
    ]);

    $payload = [
        'first_name' => 'Bob',
        'last_name' => 'Johnson',
        'email' => 'bob@example.com',
        'phone' => '+22890123458',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 10, // Only 5 available
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload)
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('Stock insuffisant');
});

it('applies percent coupon discount correctly', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'TEST20',
        'type' => 'percent',
        'value' => 20,
        'max_usage' => 100,
        'used_count' => 0,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $payload = [
        'first_name' => 'Alice',
        'last_name' => 'Brown',
        'email' => 'alice@example.com',
        'phone' => '+22890123459',
        'delivery_method' => 'email',
        'coupon_code' => 'TEST20',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2, // 2 x 10,000 = 20,000
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.totalAmount', '16000.00'); // 20% off = 16,000

    $coupon->refresh();
    expect($coupon->used_count)->toBe(1);
});

it('applies fixed coupon discount correctly', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 15000,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'FIXED5K',
        'type' => 'fixed',
        'value' => 5000,
        'max_usage' => 100,
        'used_count' => 0,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $payload = [
        'first_name' => 'Charlie',
        'last_name' => 'Davis',
        'email' => 'charlie@example.com',
        'phone' => '+22890123460',
        'delivery_method' => 'email',
        'coupon_code' => 'FIXED5K',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2, // 2 x 15,000 = 30,000
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.totalAmount', '25000.00'); // 30,000 - 5,000 = 25,000
});

it('rejects invalid coupon code', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $payload = [
        'first_name' => 'Dave',
        'last_name' => 'Wilson',
        'email' => 'dave@example.com',
        'phone' => '+22890123461',
        'delivery_method' => 'email',
        'coupon_code' => 'INVALID',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    // Coupon validation happens in Form Request, so expect standard Laravel validation error
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['coupon_code']);
});

it('rejects expired coupon', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);

    $coupon = Coupon::factory()->create([
        'code' => 'EXPIRED',
        'type' => 'percent',
        'value' => 20,
        'max_usage' => 100,
        'used_count' => 0,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
    ]);

    $payload = [
        'first_name' => 'Eve',
        'last_name' => 'Martinez',
        'email' => 'eve@example.com',
        'phone' => '+22890123462',
        'delivery_method' => 'email',
        'coupon_code' => 'EXPIRED',
        'items' => [
            [
                'ticket_type_id' => $ticketType->id,
                'quantity' => 1,
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload)
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('pas valide');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/orders', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'phone', 'delivery_method', 'items']);
});

it('calculates total amount correctly with multiple items', function (): void {
    $event = Event::factory()->create();
    $ticketType1 = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'price' => 10000,
    ]);
    $ticketType2 = TicketType::factory()->for($event)->create([
        'quantity' => 50,
        'sold_quantity' => 0,
        'price' => 25000,
    ]);

    $payload = [
        'first_name' => 'Frank',
        'last_name' => 'Garcia',
        'email' => 'frank@example.com',
        'phone' => '+22890123463',
        'delivery_method' => 'email',
        'items' => [
            [
                'ticket_type_id' => $ticketType1->id,
                'quantity' => 3, // 3 x 10,000 = 30,000
            ],
            [
                'ticket_type_id' => $ticketType2->id,
                'quantity' => 2, // 2 x 25,000 = 50,000
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/orders', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.totalAmount', '80000.00'); // 30,000 + 50,000
});
