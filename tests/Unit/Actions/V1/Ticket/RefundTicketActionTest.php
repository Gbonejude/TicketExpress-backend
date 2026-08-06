<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\V1\Ticket;

use App\Actions\V1\Ticket\RefundTicketAction;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RefundTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private RefundTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RefundTicketAction;
    }

    public function test_refunds_ticket_successfully_when_conditions_met(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(31),
            'status' => EventStatus::PUBLISHED,
        ]);

        $ticketType = TicketType::factory()->for($event)->create([
            'price' => 10000,
        ]);

        $order = Order::factory()->create([
            'total_amount' => 10000,
            'status' => OrderStatus::PAID,
        ]);

        $ticket = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::VALID,
        ]);

        $refundedTicket = $this->action->execute([
            'ticket' => $ticket,
            'reason' => 'Test refund',
        ]);

        $this->assertEquals(TicketStatus::REFUNDED, $refundedTicket->status);
        $this->assertEquals('Test refund', $refundedTicket->refund_reason);
        $this->assertNotNull($refundedTicket->refunded_at);
    }

    public function test_prevents_refund_when_less_than_30_days(): void
    {
        // The 30-day window is no longer hardcoded in the action: it comes from
        // the event's own `refund_days_before`, which the column defaults to 0.
        // Without setting it, the deadline this test is about does not exist.
        $event = Event::factory()->create([
            'start_date' => now()->addDays(29),
            'status' => EventStatus::PUBLISHED,
            'refund_allowed' => true,
            'refund_days_before' => 30,
        ]);

        $ticketType = TicketType::factory()->for($event)->create([
            'price' => 10000,
        ]);

        $order = Order::factory()->create([
            'total_amount' => 10000,
            'status' => OrderStatus::PAID,
        ]);

        $ticket = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::VALID,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Les remboursements doivent être demandés au moins 30 jour(s) avant l\'événement.');

        $this->action->execute([
            'ticket' => $ticket,
        ]);
    }

    public function test_prevents_refund_when_coupon_was_used(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(31),
            'status' => EventStatus::PUBLISHED,
        ]);

        $ticketType = TicketType::factory()->for($event)->create([
            'price' => 10000,
        ]);

        // Order with discount (coupon was used)
        $order = Order::factory()->create([
            'total_amount' => 8000, // Less than ticket price
            'status' => OrderStatus::PAID,
        ]);

        $ticket = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::VALID,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Les tickets achetés avec un code promo ne sont pas remboursables.');

        $this->action->execute([
            'ticket' => $ticket,
        ]);
    }

    public function test_prevents_refund_when_ticket_status_invalid(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(31),
        ]);

        $ticketType = TicketType::factory()->for($event)->create();
        $order = Order::factory()->create();

        $ticket = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::USED,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce ticket ne peut pas être remboursé (statut invalide).');

        $this->action->execute([
            'ticket' => $ticket,
        ]);
    }

    public function test_force_refund_bypasses_voluntary_rules(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(10), // Less than 30 days
            'status' => EventStatus::CANCELLED,
        ]);

        $ticketType = TicketType::factory()->for($event)->create([
            'price' => 10000,
        ]);

        $order = Order::factory()->create([
            'total_amount' => 8000, // Coupon was used
            'status' => OrderStatus::PAID,
        ]);

        $ticket = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::VALID,
        ]);

        $refundedTicket = $this->action->execute([
            'ticket' => $ticket,
            'reason' => 'Event cancelled',
            'force_refund' => true,
        ]);

        $this->assertEquals(TicketStatus::REFUNDED, $refundedTicket->status);
    }

    public function test_updates_order_to_refunded_when_all_tickets_refunded(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(31),
        ]);

        $ticketType = TicketType::factory()->for($event)->create([
            'price' => 10000,
        ]);

        $order = Order::factory()->create([
            'total_amount' => 20000,
            'status' => OrderStatus::PAID,
        ]);

        $ticket1 = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::VALID,
        ]);

        $ticket2 = Ticket::factory()->for($order)->for($ticketType)->create([
            'status' => TicketStatus::REFUNDED,
        ]);

        $this->action->execute([
            'ticket' => $ticket1,
        ]);

        $order->refresh();
        $this->assertEquals(OrderStatus::REFUNDED, $order->status);
        $this->assertNotNull($order->refunded_at);
    }
}
