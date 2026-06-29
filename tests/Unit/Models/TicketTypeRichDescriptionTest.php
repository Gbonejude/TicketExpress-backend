<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketTypeRichDescriptionTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = Event::factory()->create();
    }

    public function test_ticket_type_can_have_benefits_array(): void
    {
        $benefits = [
            '🍾 Boissons offertes',
            '🍽️ Buffet inclus',
            '🎟️ Accès backstage',
        ];

        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'benefits' => $benefits,
        ]);

        $this->assertIsArray($ticketType->benefits);
        $this->assertCount(3, $ticketType->benefits);
        $this->assertEquals($benefits, $ticketType->benefits);
    }

    public function test_benefits_can_be_null(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'benefits' => null,
        ]);

        $this->assertNull($ticketType->benefits);
    }

    public function test_benefits_can_be_empty_array(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'benefits' => [],
        ]);

        $this->assertIsArray($ticketType->benefits);
        $this->assertEmpty($ticketType->benefits);
    }

    public function test_ticket_type_can_have_location_details(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'location_details' => 'Loges VIP avec vue directe sur scène',
        ]);

        $this->assertEquals('Loges VIP avec vue directe sur scène', $ticketType->location_details);
    }

    public function test_location_details_can_be_null(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'location_details' => null,
        ]);

        $this->assertNull($ticketType->location_details);
    }

    public function test_ticket_type_can_be_featured(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'is_featured' => true,
        ]);

        $this->assertTrue($ticketType->is_featured);
    }

    public function test_ticket_type_not_featured_by_default(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'is_featured' => false, // Explicitly set to false
        ]);

        $this->assertFalse($ticketType->is_featured);
    }

    public function test_ticket_type_has_sort_order(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'sort_order' => 10,
        ]);

        $this->assertEquals(10, $ticketType->sort_order);
    }

    public function test_sort_order_can_be_set(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'sort_order' => 0, // Explicitly set to 0
        ]);

        $this->assertEquals(0, $ticketType->sort_order);
    }

    public function test_can_query_featured_tickets(): void
    {
        TicketType::factory()->count(3)->create([
            'event_id' => $this->event->id,
            'is_featured' => false,
        ]);

        TicketType::factory()->count(2)->create([
            'event_id' => $this->event->id,
            'is_featured' => true,
        ]);

        $featured = TicketType::where('is_featured', true)->get();

        $this->assertCount(2, $featured);
        $this->assertTrue($featured->every(fn ($ticket) => $ticket->is_featured));
    }

    public function test_can_order_tickets_by_sort_order(): void
    {
        $ticket1 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Standard',
            'sort_order' => 5,
        ]);

        $ticket2 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'VIP',
            'sort_order' => 10,
        ]);

        $ticket3 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Étudiant',
            'sort_order' => 1,
        ]);

        $orderedTickets = TicketType::where('event_id', $this->event->id)
            ->orderBy('sort_order', 'desc')
            ->get();

        $this->assertEquals('VIP', $orderedTickets->first()->name);
        $this->assertEquals('Standard', $orderedTickets->get(1)->name);
        $this->assertEquals('Étudiant', $orderedTickets->last()->name);
    }

    public function test_vip_factory_state_has_full_rich_description(): void
    {
        $vipTicket = TicketType::factory()->vip()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals('VIP', $vipTicket->name);
        $this->assertTrue($vipTicket->is_featured);
        $this->assertEquals(10, $vipTicket->sort_order);
        $this->assertNotNull($vipTicket->benefits);
        $this->assertIsArray($vipTicket->benefits);
        $this->assertNotEmpty($vipTicket->benefits);
        $this->assertNotNull($vipTicket->location_details);
        $this->assertStringContainsString('VIP', $vipTicket->location_details);
    }

    public function test_standard_factory_state_has_correct_attributes(): void
    {
        $standardTicket = TicketType::factory()->standard()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals('Standard', $standardTicket->name);
        $this->assertFalse($standardTicket->is_featured);
        $this->assertEquals(5, $standardTicket->sort_order);
        $this->assertNotNull($standardTicket->benefits);
        $this->assertIsArray($standardTicket->benefits);
    }

    public function test_student_factory_state_has_lower_sort_order(): void
    {
        $studentTicket = TicketType::factory()->student()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertEquals('Étudiant', $studentTicket->name);
        $this->assertFalse($studentTicket->is_featured);
        $this->assertEquals(1, $studentTicket->sort_order);
        $this->assertStringContainsString('Tarif réduit', $studentTicket->description);
    }

    public function test_featured_state_sets_is_featured_true(): void
    {
        $ticket = TicketType::factory()->featured()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertTrue($ticket->is_featured);
        $this->assertEquals(10, $ticket->sort_order);
    }

    public function test_not_featured_state_sets_is_featured_false(): void
    {
        $ticket = TicketType::factory()->notFeatured()->create([
            'event_id' => $this->event->id,
        ]);

        $this->assertFalse($ticket->is_featured);
    }

    public function test_benefits_are_cast_to_array(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'benefits' => ['Benefit 1', 'Benefit 2'],
        ]);

        // Refresh from database to test casting
        $ticketType->refresh();

        $this->assertIsArray($ticketType->benefits);
        $this->assertCount(2, $ticketType->benefits);
    }

    public function test_is_featured_is_cast_to_boolean(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'is_featured' => 1, // Store as integer
        ]);

        $ticketType->refresh();

        $this->assertIsBool($ticketType->is_featured);
        $this->assertTrue($ticketType->is_featured);
    }

    public function test_sort_order_is_cast_to_integer(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'sort_order' => '15', // Store as string
        ]);

        $ticketType->refresh();

        $this->assertIsInt($ticketType->sort_order);
        $this->assertEquals(15, $ticketType->sort_order);
    }
}
