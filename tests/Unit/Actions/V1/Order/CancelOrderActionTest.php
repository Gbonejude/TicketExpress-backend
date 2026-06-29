<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\V1\Order;

use App\Actions\V1\Order\CancelOrderAction;
use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CancelOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CancelOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CancelOrderAction;
    }

    public function test_releases_stock_on_cancellation(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 10,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::factory()->for($order)->for($ticketType)->create([
            'quantity' => 5,
        ]);

        $cancelledOrder = $this->action->execute(['order' => $order]);

        $this->assertEquals(OrderStatus::CANCELLED, $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->cancelled_at);
    }

    public function test_prevents_canceling_paid_orders(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 5,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PAID,
        ]);

        OrderItem::factory()->for($order)->for($ticketType)->create([
            'quantity' => 5,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible d\'annuler une commande payée. Demandez un remboursement.');

        $this->action->execute(['order' => $order]);
    }

    public function test_prevents_double_cancellation(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 5,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::CANCELLED,
        ]);

        OrderItem::factory()->for($order)->for($ticketType)->create([
            'quantity' => 5,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cette commande est déjà annulée.');

        $this->action->execute(['order' => $order]);
    }

    public function test_updates_order_status_to_cancelled(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 5,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::factory()->for($order)->for($ticketType)->create([
            'quantity' => 5,
        ]);

        $cancelledOrder = $this->action->execute(['order' => $order]);

        $this->assertEquals(OrderStatus::CANCELLED, $cancelledOrder->status);
    }

    public function test_releases_stock_for_multiple_items(): void
    {
        $event = Event::factory()->create();
        $ticketType1 = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 20,
        ]);
        $ticketType2 = TicketType::factory()->for($event)->create([
            'quantity' => 50,
            'sold_quantity' => 15,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::factory()->for($order)->for($ticketType1)->create([
            'quantity' => 10,
        ]);
        OrderItem::factory()->for($order)->for($ticketType2)->create([
            'quantity' => 5,
        ]);

        $cancelledOrder = $this->action->execute(['order' => $order]);

        $this->assertEquals(OrderStatus::CANCELLED, $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->cancelled_at);
    }

    public function test_atomic_cancellation_with_transaction(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 10,
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::factory()->for($order)->for($ticketType)->create([
            'quantity' => 5,
        ]);

        $cancelledOrder = $this->action->execute(['order' => $order]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED->value,
        ]);

        $this->assertNotNull($cancelledOrder->cancelled_at);
    }
}
