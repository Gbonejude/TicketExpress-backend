<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AvailabilityStatus;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketTypeAvailabilityStatusTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::factory()->create();
    }

    public function test_available_status_when_less_than_70_percent_sold(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 50, // 50% sold
        ]);

        $this->assertEquals(AvailabilityStatus::AVAILABLE, $ticketType->availabilityStatus());
        $this->assertEquals('Disponible', $ticketType->availabilityStatus()->label());
        $this->assertEquals('green', $ticketType->availabilityStatus()->color());
        $this->assertTrue($ticketType->availabilityStatus()->isAvailableForPurchase());
    }

    public function test_high_demand_status_when_70_to_84_percent_sold(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 75, // 75% sold
        ]);

        $this->assertEquals(AvailabilityStatus::HIGH_DEMAND, $ticketType->availabilityStatus());
        $this->assertEquals('Forte demande', $ticketType->availabilityStatus()->label());
        $this->assertEquals('orange', $ticketType->availabilityStatus()->color());
        $this->assertEquals('🔥', $ticketType->availabilityStatus()->icon());
        $this->assertEquals(2, $ticketType->availabilityStatus()->urgencyLevel());
    }

    public function test_running_out_status_when_85_to_94_percent_sold(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 90, // 90% sold
        ]);

        $this->assertEquals(AvailabilityStatus::RUNNING_OUT, $ticketType->availabilityStatus());
        $this->assertEquals('En voie d\'épuisement', $ticketType->availabilityStatus()->label());
        $this->assertEquals('red', $ticketType->availabilityStatus()->color());
        $this->assertEquals('⚠️', $ticketType->availabilityStatus()->icon());
        $this->assertEquals(3, $ticketType->availabilityStatus()->urgencyLevel());
    }

    public function test_almost_sold_out_status_when_95_percent_or_more_sold(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 97, // 97% sold
        ]);

        $this->assertEquals(AvailabilityStatus::ALMOST_SOLD_OUT, $ticketType->availabilityStatus());
        $this->assertEquals('Bientôt épuisé', $ticketType->availabilityStatus()->label());
        $this->assertEquals('red', $ticketType->availabilityStatus()->color());
        $this->assertEquals('🚨', $ticketType->availabilityStatus()->icon());
        $this->assertEquals(5, $ticketType->availabilityStatus()->urgencyLevel());
    }

    public function test_limited_status_when_less_than_50_tickets_remaining(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 60,
            'sold_quantity' => 30, // 30 remaining (< 50)
        ]);

        $this->assertEquals(AvailabilityStatus::LIMITED, $ticketType->availabilityStatus());
        $this->assertEquals('Places limitées', $ticketType->availabilityStatus()->label());
        $this->assertEquals('yellow', $ticketType->availabilityStatus()->color());
        $this->assertEquals('⏰', $ticketType->availabilityStatus()->icon());
        $this->assertEquals(4, $ticketType->availabilityStatus()->urgencyLevel());
    }

    public function test_sold_out_status_when_no_tickets_remaining(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 100, // 100% sold
        ]);

        $this->assertEquals(AvailabilityStatus::SOLD_OUT, $ticketType->availabilityStatus());
        $this->assertEquals('Complet', $ticketType->availabilityStatus()->label());
        $this->assertEquals('gray', $ticketType->availabilityStatus()->color());
        $this->assertEquals('❌', $ticketType->availabilityStatus()->icon());
        $this->assertFalse($ticketType->availabilityStatus()->isAvailableForPurchase());
        $this->assertEquals(0, $ticketType->availabilityStatus()->urgencyLevel());
    }

    public function test_remaining_tickets_calculation(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 75,
        ]);

        $this->assertEquals(25, $ticketType->remainingTickets());
    }

    public function test_remaining_tickets_cannot_be_negative(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 150, // Oversold (should not happen, but testing safety)
        ]);

        $this->assertEquals(0, $ticketType->remainingTickets());
    }

    public function test_sold_percentage_calculation(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 75,
        ]);

        $this->assertEquals(75.0, $ticketType->soldPercentage());
    }

    public function test_availability_percentage_calculation(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 75,
        ]);

        $this->assertEquals(25.0, $ticketType->availabilityPercentage());
    }

    public function test_percentage_calculations_handle_zero_quantity(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 0,
            'sold_quantity' => 0,
        ]);

        $this->assertEquals(0.0, $ticketType->soldPercentage());
        $this->assertEquals(0.0, $ticketType->availabilityPercentage());
    }

    public function test_limited_takes_priority_when_below_50_and_low_percentage(): void
    {
        // If sold percentage is low AND < 50 remaining, LIMITED should trigger
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 60, // 60% sold (below 70% threshold)
        ]);

        // 40 remaining < 50, and sold% < 70%, so should be LIMITED
        $this->assertEquals(AvailabilityStatus::LIMITED, $ticketType->availabilityStatus());
    }

    public function test_percentage_takes_priority_over_limited_when_high(): void
    {
        // If sold percentage is high (>=70%), percentage-based status takes priority
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 50,
            'sold_quantity' => 40, // 80% sold = HIGH_DEMAND
        ]);

        // 10 remaining < 50, but 80% sold = HIGH_DEMAND takes priority
        $this->assertEquals(AvailabilityStatus::HIGH_DEMAND, $ticketType->availabilityStatus());
    }

    public function test_sold_out_takes_priority_over_all_other_statuses(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 30, // Small quantity (would trigger LIMITED if any remaining)
            'sold_quantity' => 30, // But completely sold out
        ]);

        $this->assertEquals(AvailabilityStatus::SOLD_OUT, $ticketType->availabilityStatus());
    }

    public function test_badge_class_matches_status(): void
    {
        $available = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 30,
        ]);

        $soldOut = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'quantity' => 100,
            'sold_quantity' => 100,
        ]);

        $this->assertEquals('badge-success', $available->availabilityStatus()->badgeClass());
        $this->assertEquals('badge-secondary', $soldOut->availabilityStatus()->badgeClass());
    }
}
