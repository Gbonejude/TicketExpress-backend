<?php

declare(strict_types=1);

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows guests to reach the payment initiation endpoint', function (): void {
    $this->postJson('/api/v1/payments/initiate', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['order_id', 'phone_number', 'network']);
});

it('allows guests to reach the payment status endpoint', function (): void {
    $this->getJson('/api/v1/payments/01ARZ3NDEKTSV4RRFFQ69G5FAV/status')
        ->assertNotFound();
});

it('creates a pending payment without requiring an existing transaction reference', function (): void {
    Http::fake([
        '*' => Http::response([
            'tx_reference' => 'TXN-123456',
            'status' => 0,
        ], 200),
    ]);

    $order = Order::factory()->guest()->create([
        'total_amount' => 2000,
        'status' => OrderStatus::PENDING,
        'payment_method' => PaymentMethod::TMONEY,
        'delivery_method' => DeliveryMethod::EMAIL,
    ]);

    $this->postJson('/api/v1/payments/initiate', [
        'order_id' => $order->id,
        'phone_number' => '+22890123456',
        'network' => 'TMONEY',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.txReference', 'TXN-123456');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'amount' => '2000.00',
        'method' => 'tmoney',
        'transaction_reference' => 'TXN-123456',
    ]);
});
