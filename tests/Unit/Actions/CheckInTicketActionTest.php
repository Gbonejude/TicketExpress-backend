<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\V1\Ticket\CheckInTicketAction;
use App\Enums\TicketStatus;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Models\Event as EventModel;
use App\Models\Ticket;
use App\Models\TicketType;
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

    /**
     * Un billet dont l'événement se déroule maintenant.
     *
     * La validation manuelle obéit à la même fenêtre horaire que le portique.
     * L'événement par défaut de la factory est dans plusieurs semaines : sans
     * cette précaution, chaque test de cette classe se heurterait à un refus
     * « trop tôt » au lieu du comportement qu'il vérifie.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function ticketForOngoingEvent(array $attributes = [], string $start = '-1 hour', string $end = '+2 hours'): Ticket
    {
        $event = EventModel::factory()->create([
            'start_date' => now()->parse($start),
            'end_date' => now()->parse($end),
        ]);

        return Ticket::factory()->create([
            'ticket_type_id' => TicketType::factory()->create(['event_id' => $event->id])->id,
            ...$attributes,
        ]);
    }

    /** @test */
    public function it_checks_in_a_valid_ticket(): void
    {
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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
        $ticket = $this->ticketForOngoingEvent([
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

    /**
     * La validation manuelle est le chemin de contournement du portique : plus
     * permissive, elle deviendrait le seul emprunté, et la fenêtre ne
     * protégerait plus rien.
     *
     * @test
     */
    public function it_refuses_to_check_in_before_the_window_opens(): void
    {
        $ticket = $this->ticketForOngoingEvent(
            ['status' => TicketStatus::VALID, 'checked_in_at' => null],
            start: '+3 days',
            end: '+3 days 4 hours',
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Trop tôt/');

        try {
            $this->action->execute(['ticket' => $ticket]);
        } finally {
            $this->assertNull($ticket->fresh()->checked_in_at);
        }
    }

    /** @test */
    public function it_refuses_to_check_in_after_the_window_closed(): void
    {
        $ticket = $this->ticketForOngoingEvent(
            ['status' => TicketStatus::VALID, 'checked_in_at' => null],
            start: '-3 days',
            end: '-3 days +4 hours',
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Trop tard/');

        try {
            $this->action->execute(['ticket' => $ticket]);
        } finally {
            $this->assertNull($ticket->fresh()->checked_in_at);
        }
    }

    /** @test */
    public function it_refuses_a_ticket_that_belongs_to_no_event(): void
    {
        $ticket = $this->ticketForOngoingEvent([
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);

        // Sans événement, la fenêtre n'est pas vérifiable : refuser vaut mieux
        // que laisser passer faute de date. La situation ne s'atteint pas en
        // base — `tickets.ticket_type_id` est en `restrict` — donc on force la
        // relation à vide, ce que produirait une donnée corrompue.
        $ticket->setRelation('ticketType', null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ce ticket n\'est rattaché à aucun événement.');

        $this->action->execute(['ticket' => $ticket]);
    }
}
