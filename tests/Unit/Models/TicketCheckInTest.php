<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * @group checkin
 * @group feature3
 */
final class TicketCheckInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_when_ticket_is_not_checked_in(): void
    {
        $ticket = Ticket::factory()->create([
            'checked_in_at' => null,
        ]);

        $this->assertFalse($ticket->isCheckedIn());
    }

    /** @test */
    public function it_returns_true_when_ticket_is_checked_in(): void
    {
        $ticket = Ticket::factory()->create([
            'checked_in_at' => now(),
        ]);

        $this->assertTrue($ticket->isCheckedIn());
    }

    /** @test */
    public function it_returns_true_for_online_access_method(): void
    {
        $ticket = Ticket::factory()->create([
            'access_method' => 'online',
        ]);

        $this->assertTrue($ticket->isOnlineAccess());
        $this->assertFalse($ticket->isPhysicalAccess());
    }

    /** @test */
    public function it_returns_true_for_physical_access_method(): void
    {
        $ticket = Ticket::factory()->create([
            'access_method' => 'physical',
        ]);

        $this->assertTrue($ticket->isPhysicalAccess());
        $this->assertFalse($ticket->isOnlineAccess());
    }

    /** @test */
    public function it_returns_false_for_invalid_access_method(): void
    {
        $ticket = Ticket::factory()->create([
            'access_method' => 'invalid', // Not 'online', 'physical', or 'hybrid'
        ]);

        $this->assertFalse($ticket->isOnlineAccess());
        $this->assertFalse($ticket->isPhysicalAccess());
    }

    /** @test */
    public function it_stores_checked_in_by_information(): void
    {
        $ticket = Ticket::factory()->create([
            'checked_in_at' => now(),
            'checked_in_by' => 'Staff #123',
        ]);

        $this->assertEquals('Staff #123', $ticket->checked_in_by);
    }

    /** @test */
    public function it_stores_online_access_link(): void
    {
        $ticket = Ticket::factory()->create([
            'access_method' => 'online',
            'online_access_link' => 'https://zoom.us/j/123456789',
        ]);

        $this->assertEquals('https://zoom.us/j/123456789', $ticket->online_access_link);
    }

    /** @test */
    public function checked_in_at_is_cast_to_datetime(): void
    {
        $ticket = Ticket::factory()->create([
            'checked_in_at' => '2024-01-15 10:30:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $ticket->checked_in_at);
    }

    /** @test */
    public function it_can_have_access_method_hybrid(): void
    {
        $ticket = Ticket::factory()->create([
            'access_method' => 'hybrid',
            'online_access_link' => 'https://teams.microsoft.com/meeting/xyz',
        ]);

        $this->assertEquals('hybrid', $ticket->access_method);
        $this->assertTrue($ticket->isOnlineAccess());
        $this->assertTrue($ticket->isPhysicalAccess());
    }
}
