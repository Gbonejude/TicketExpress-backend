<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class EventOccurrenceTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::factory()->create();
    }

    public function test_occurrence_belongs_to_event(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertInstanceOf(Event::class, $occurrence->event);
        $this->assertEquals($this->event->id, $occurrence->event->id);
    }

    public function test_occurrence_has_many_ticket_types(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
        ]);

        TicketType::factory()->count(3)->create([
            'event_id' => $this->event->id,
            'occurrence_id' => $occurrence->id,
        ]);

        $this->assertCount(3, $occurrence->ticketTypes);
        $this->assertInstanceOf(TicketType::class, $occurrence->ticketTypes->first());
    }

    public function test_event_has_many_occurrences(): void
    {
        EventOccurrence::factory()->count(3)->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertCount(3, $this->event->occurrences);
        $this->assertInstanceOf(EventOccurrence::class, $this->event->occurrences->first());
    }

    public function test_availability_percentage_with_max_attendees(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 100,
            'current_attendees' => 30,
        ]);

        $this->assertEquals(70.0, $occurrence->availabilityPercentage());
    }

    public function test_availability_percentage_with_null_max_attendees(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => null,
            'current_attendees' => 50,
        ]);

        $this->assertEquals(100.0, $occurrence->availabilityPercentage());
    }

    public function test_availability_percentage_with_zero_max_attendees(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 0,
            'current_attendees' => 0,
        ]);

        $this->assertEquals(100.0, $occurrence->availabilityPercentage());
    }

    public function test_remaining_capacity_calculation(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 100,
            'current_attendees' => 65,
        ]);

        $this->assertEquals(35, $occurrence->remainingCapacity());
    }

    public function test_remaining_capacity_returns_null_when_unlimited(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => null,
            'current_attendees' => 500,
        ]);

        $this->assertNull($occurrence->remainingCapacity());
    }

    public function test_remaining_capacity_cannot_be_negative(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 100,
            'current_attendees' => 150, // Oversold
        ]);

        $this->assertEquals(0, $occurrence->remainingCapacity());
    }

    public function test_is_sold_out_when_status_is_sold_out(): void
    {
        $occurrence = EventOccurrence::factory()->soldOut()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertTrue($occurrence->isSoldOut());
    }

    public function test_is_sold_out_when_current_equals_max(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 100,
            'current_attendees' => 100,
        ]);

        $this->assertTrue($occurrence->isSoldOut());
    }

    public function test_is_not_sold_out_when_capacity_available(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => 100,
            'current_attendees' => 50,
        ]);

        $this->assertFalse($occurrence->isSoldOut());
    }

    public function test_is_not_sold_out_when_unlimited(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => null,
            'current_attendees' => 1000,
        ]);

        $this->assertFalse($occurrence->isSoldOut());
    }

    public function test_is_active_when_status_is_active(): void
    {
        $occurrence = EventOccurrence::factory()->active()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertTrue($occurrence->isActive());
        $this->assertEquals('active', $occurrence->status);
    }

    public function test_is_not_active_when_cancelled(): void
    {
        $occurrence = EventOccurrence::factory()->cancelled()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertFalse($occurrence->isActive());
        $this->assertEquals('cancelled', $occurrence->status);
    }

    public function test_factory_active_state(): void
    {
        $occurrence = EventOccurrence::factory()->active()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals('active', $occurrence->status);
    }

    public function test_factory_sold_out_state(): void
    {
        $occurrence = EventOccurrence::factory()
            ->withCapacity(50)
            ->soldOut()
            ->create([
                'event_id' => $this->event->id,
            ]);

        $this->assertEquals('sold_out', $occurrence->status);
        $this->assertEquals(50, $occurrence->current_attendees);
        $this->assertTrue($occurrence->isSoldOut());
    }

    public function test_factory_cancelled_state(): void
    {
        $occurrence = EventOccurrence::factory()->cancelled()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals('cancelled', $occurrence->status);
    }

    public function test_factory_unlimited_state(): void
    {
        $occurrence = EventOccurrence::factory()->unlimited()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertNull($occurrence->max_attendees);
        $this->assertNull($occurrence->remainingCapacity());
    }

    public function test_factory_with_capacity_state(): void
    {
        $occurrence = EventOccurrence::factory()->withCapacity(200)->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals(200, $occurrence->max_attendees);
        $this->assertEquals(0, $occurrence->current_attendees);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertInstanceOf(Carbon::class, $occurrence->start_date);
        $this->assertInstanceOf(Carbon::class, $occurrence->end_date);
    }

    public function test_attendees_are_cast_to_integers(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
            'max_attendees' => '100',
            'current_attendees' => '50',
        ]);

        $occurrence->refresh();

        $this->assertIsInt($occurrence->max_attendees);
        $this->assertIsInt($occurrence->current_attendees);
    }

    public function test_ticket_type_can_reference_occurrence(): void
    {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $this->event->id,
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'occurrence_id' => $occurrence->id,
        ]);

        $this->assertEquals($occurrence->id, $ticketType->occurrence_id);
        $this->assertInstanceOf(EventOccurrence::class, $ticketType->occurrence);
    }

    public function test_ticket_type_occurrence_can_be_null(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'occurrence_id' => null,
        ]);

        $this->assertNull($ticketType->occurrence_id);
    }
}
