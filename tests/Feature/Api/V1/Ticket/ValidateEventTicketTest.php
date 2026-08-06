<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\CheckIn;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Un billet valide, rattaché à un événement d'un organisateur donné.
 *
 * @return array{0: Event, 1: Ticket}
 */
function eventWithTicket(?Organizer $organizer = null): array
{
    $organizer ??= Organizer::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
    $order = Order::factory()->paid()->create();

    $ticket = Ticket::factory()->create([
        'order_id' => $order->id,
        'ticket_type_id' => $ticketType->id,
        'status' => TicketStatus::VALID,
        'checked_in_at' => null,
        'checked_in_by' => null,
    ]);

    return [$event, $ticket];
}

function staff(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role);
    $user->assignRole($role);

    return $user;
}

it('validates a ticket and records who scanned it', function (): void {
    [$event, $ticket] = eventWithTicket();
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok')
        ->assertJsonPath('data.ticket.ticketNumber', $ticket->ticket_number);

    $ticket->refresh();

    expect($ticket->checked_in_at)->not->toBeNull()
        ->and($ticket->checked_in_by)->toBe($agent->id);

    // Une ligne d'historique est écrite : la table check_ins n'était alimentée
    // par personne avant.
    $this->assertDatabaseHas('check_ins', [
        'ticket_id' => $ticket->id,
        'scanned_by' => $agent->id,
    ]);
});

it('accepts the qr payload', function (): void {
    [$event, $ticket] = eventWithTicket();

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('refuses the printed ticket number as an entry code', function (): void {
    // Le numéro est séquentiel (AFRO-2026-0042), donc devinable : l'accepter
    // laisserait entrer qui sait compter. Seul le QR autorise l'entrée.
    [$event, $ticket] = eventWithTicket();

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->ticket_number])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'not_found');

    expect($ticket->fresh()->checked_in_at)->toBeNull();
});

it('refuses a ticket that has already been validated', function (): void {
    [$event, $ticket] = eventWithTicket();
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk();

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'already_used');

    // Un rejeu ne doit pas doubler l'historique.
    expect(CheckIn::where('ticket_id', $ticket->id)->count())->toBe(1);
});

it('refuses a ticket issued for another event', function (): void {
    [, $ticket] = eventWithTicket();
    $otherEvent = Event::factory()->create();

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$otherEvent->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'wrong_event');

    expect($ticket->fresh()->checked_in_at)->toBeNull();
});

it('refuses an unknown code', function (): void {
    [$event] = eventWithTicket();

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => 'PAS-UN-BILLET'])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'not_found');
});

it('refuses a refunded ticket', function (): void {
    [$event, $ticket] = eventWithTicket();
    $ticket->update(['status' => TicketStatus::REFUNDED]);

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'not_valid');
});

it('lets the event organizer validate their own tickets', function (): void {
    $organizer = Organizer::factory()->create();
    [$event, $ticket] = eventWithTicket($organizer);

    $manager = staff('organizer-manager');
    $organizer->update(['user_id' => $manager->id]);

    $this->actingAs($manager)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('stops an organizer from validating tickets of another organizer', function (): void {
    $mine = Organizer::factory()->create();
    $manager = staff('organizer-manager');
    $mine->update(['user_id' => $manager->id]);

    [$foreignEvent, $foreignTicket] = eventWithTicket();

    $this->actingAs($manager)
        ->postJson("/api/v1/events/{$foreignEvent->id}/tickets/validate", ['code' => $foreignTicket->qr_code])
        ->assertForbidden();

    expect($foreignTicket->fresh()->checked_in_at)->toBeNull();
});

it('keeps a participant away from the validation endpoint', function (): void {
    [$event, $ticket] = eventWithTicket();

    $this->actingAs(staff('participant'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertForbidden();
});

it('returns the recent check-ins of an event', function (): void {
    [$event, $ticket] = eventWithTicket();
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk();

    $this->actingAs($agent)
        ->getJson("/api/v1/events/{$event->id}/check-ins")
        ->assertOk()
        ->assertJsonCount(1, 'data.checkIns')
        ->assertJsonPath('data.checkIns.0.ticketNumber', $ticket->ticket_number);
});
