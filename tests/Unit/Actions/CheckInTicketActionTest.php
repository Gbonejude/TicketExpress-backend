<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\V1\Ticket\CheckInTicketAction;
use App\Enums\TicketStatus;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group checkin
 * @group feature3
 * @group actions
 */
final class CheckInTicketActionTest extends TestCase
{
    use RefreshDatabase;

    private CheckInTicketAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CheckInTicketAction;
        Event::fake();
    }

    /** @test */
    public function it_checks_in_a_valid_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);

        $result = $this->action->execute([
            'ticket' => $ticket,
            'checked_in_by' => 'Staff #456',
        ]);

        $this->assertTrue($result->isCheckedIn());
        $this->assertNotNull($result->checked_in_at);
        $this->assertEquals('Staff #456', $result->checked_in_by);

        Event::assertDispatched(TicketCheckedInEvent::class);
    }

    /** @test */
    public function it_throws_exception_when_ticket_already_checked_in(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::VALID,
            'checked_in_at' => now()->subHour(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ce ticket a déjà été scanné.');

        $this->action->execute(['ticket' => $ticket]);
    }

    /** @test */
    public function it_throws_exception_when_ticket_is_not_active(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::CANCELLED,
            'checked_in_at' => null,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ce ticket n\'est pas valide pour le check-in.');

        $this->action->execute(['ticket' => $ticket]);
    }

    /** @test */
    public function it_throws_exception_when_ticket_is_refunded(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::REFUNDED,
            'checked_in_at' => null,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ce ticket n\'est pas valide pour le check-in.');

        $this->action->execute(['ticket' => $ticket]);
    }

    /** @test */
    public function it_checks_in_ticket_without_checked_in_by_information(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);

        $result = $this->action->execute(['ticket' => $ticket]);

        $this->assertTrue($result->isCheckedIn());
        $this->assertNotNull($result->checked_in_at);
        $this->assertNull($result->checked_in_by);
    }

    /** @test */
    public function it_dispatches_ticket_checked_in_event(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);

        $this->action->execute(['ticket' => $ticket]);

        Event::assertDispatched(TicketCheckedInEvent::class, function ($event) use ($ticket) {
            return $event->ticket->id === $ticket->id;
        });
    }

    /** @test */
    public function it_refreshes_ticket_after_check_in(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);

        $result = $this->action->execute([
            'ticket' => $ticket,
            'checked_in_by' => 'Security Team',
        ]);

        // Verify the returned ticket has the updated data
        $this->assertNotNull($result->checked_in_at);
        $this->assertEquals('Security Team', $result->checked_in_by);

        // Verify database was updated
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'checked_in_by' => 'Security Team',
        ]);
    }
}
