<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

it('releases stock when order is cancelled', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 20,
        'price' => 10000,
    ]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PENDING,
        'total_amount' => 50000,
    ]);

    $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 5,
        'unit_price' => 10000,
        'subtotal' => 50000,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])
        ->postJson("/api/v1/orders/{$order->id}/cancel");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $order->refresh();
    $ticketType->refresh();

    expect($order->status)->toBe(OrderStatus::CANCELLED);
    expect($ticketType->sold_quantity)->toBe(15); // 20 - 5 = 15
});

it('prevents canceling paid orders', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 5,
        'price' => 10000,
    ]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PAID,
        'total_amount' => 50000,
    ]);

    $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 5,
        'unit_price' => 10000,
        'subtotal' => 50000,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('payée');
});

it('prevents double cancellation', function (): void {
    $event = Event::factory()->create();
    $ticketType = TicketType::factory()->for($event)->create([
        'quantity' => 100,
        'sold_quantity' => 5,
        'price' => 10000,
    ]);

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::CANCELLED,
        'total_amount' => 50000,
    ]);

    $order->items()->create([
        'ticket_type_id' => $ticketType->id,
        'quantity' => 5,
        'unit_price' => 10000,
        'subtotal' => 50000,
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$this->token,
    ])
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('annulée');
});

it('requires authentication to cancel order', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING,
    ]);

    $this->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertUnauthorized();
});
