<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\V1\Order;

use App\Actions\V1\Order\CreateOrderAction;
use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateOrderAction;
    }

    public function test_creates_order_with_atomic_stock_management(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'payment_method' => 'stripe',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        $this->assertNotNull($order->id);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(250000, $order->total_amount);
        $this->assertCount(1, $order->items);

        $ticketType->refresh();
        $this->assertEquals(5, $ticketType->sold_quantity);
    }

    public function test_applies_percent_coupon_correctly(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $coupon = Coupon::factory()->percent(20)->create([
            'code' => 'DISCOUNT20',
            'max_usage' => 100,
            'used_count' => 0,
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'payment_method' => 'stripe',
            'coupon_code' => 'DISCOUNT20',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        // Original: 2 * 50000 = 100000
        // Discount: 20% of 100000 = 20000
        // Final: 100000 - 20000 = 80000
        $this->assertEquals(80000, $order->total_amount);

        $coupon->refresh();
        $this->assertEquals(1, $coupon->used_count);
    }

    public function test_applies_fixed_coupon_correctly(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $coupon = Coupon::factory()->fixed(10000)->create([
            'code' => 'FIXED10K',
            'max_usage' => 100,
            'used_count' => 0,
        ]);

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '+22890123457',
            'delivery_method' => 'email',
            'payment_method' => 'stripe',
            'coupon_code' => 'FIXED10K',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        // Original: 3 * 50000 = 150000
        // Discount: -10000
        // Final: 140000
        $this->assertEquals(140000, $order->total_amount);
    }

    public function test_throws_exception_on_insufficient_stock(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 10,
            'sold_quantity' => 5,
            'price' => 50000,
            'name' => 'VIP Pass',
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
                    'quantity' => 10,
                ],
            ],
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Stock insuffisant pour "VIP Pass". Disponible: 5, Demandé: 10');

        $this->action->execute($data);
    }

    public function test_transaction_rollback_on_error(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $initialSoldQuantity = $ticketType->sold_quantity;

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'coupon_code' => 'INVALID_COUPON',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 5,
                ],
            ],
        ];

        try {
            $this->action->execute($data);
            $this->fail('Expected DomainException was not thrown');
        } catch (\DomainException $e) {
            // Expected exception
        }

        $ticketType->refresh();
        $this->assertEquals($initialSoldQuantity, $ticketType->sold_quantity);
    }

    public function test_guest_checkout_with_null_user_id(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $data = [
            'user_id' => null,
            'first_name' => 'Guest',
            'last_name' => 'User',
            'email' => 'guest@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        $this->assertNull($order->user_id);
        $this->assertEquals('Guest', $order->first_name);
        $this->assertEquals('guest@example.com', $order->email);
    }

    public function test_user_order_association(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $data = [
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        $this->assertEquals($user->id, $order->user_id);
        $this->assertInstanceOf(User::class, $order->user);
    }

    public function test_validates_coupon_usage_limits(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $coupon = Coupon::factory()->create([
            'code' => 'LIMIT1',
            'max_usage' => 1,
            'used_count' => 1,
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'coupon_code' => 'LIMIT1',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo a atteint sa limite d\'utilisation.');

        $this->action->execute($data);
    }

    public function test_validates_coupon_date_range(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
        ]);

        $coupon = Coupon::factory()->expired()->create([
            'code' => 'EXPIRED',
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'coupon_code' => 'EXPIRED',
            'items' => [
                [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo n\'est pas valide pour cette période.');

        $this->action->execute($data);
    }

    public function test_creates_order_items_with_captured_price(): void
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create([
            'quantity' => 100,
            'sold_quantity' => 0,
            'price' => 50000,
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

        $orderItem = $order->items->first();
        $this->assertEquals(50000, $orderItem->unit_price);
        $this->assertEquals(3, $orderItem->quantity);
        $this->assertEquals(150000, $orderItem->subtotal);
    }

    public function test_handles_multiple_ticket_types(): void
    {
        $event = Event::factory()->create();
        $vipTicket = TicketType::factory()->for($event)->create([
            'quantity' => 50,
            'sold_quantity' => 0,
            'price' => 100000,
            'name' => 'VIP',
        ]);
        $regularTicket = TicketType::factory()->for($event)->create([
            'quantity' => 200,
            'sold_quantity' => 0,
            'price' => 25000,
            'name' => 'Regular',
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+22890123456',
            'delivery_method' => 'email',
            'items' => [
                [
                    'ticket_type_id' => $vipTicket->id,
                    'quantity' => 2,
                ],
                [
                    'ticket_type_id' => $regularTicket->id,
                    'quantity' => 5,
                ],
            ],
        ];

        $order = $this->action->execute($data);

        // (2 * 100000) + (5 * 25000) = 200000 + 125000 = 325000
        $this->assertEquals(325000, $order->total_amount);
        $this->assertCount(2, $order->items);

        $vipTicket->refresh();
        $regularTicket->refresh();
        $this->assertEquals(2, $vipTicket->sold_quantity);
        $this->assertEquals(5, $regularTicket->sold_quantity);
    }
}
