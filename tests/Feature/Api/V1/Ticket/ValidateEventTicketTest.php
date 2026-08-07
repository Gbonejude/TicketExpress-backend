<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\CheckIn;
use App\Models\Event;
use App\Models\EventOccurrence;
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
 * L'événement est **en cours** : hors de la fenêtre de contrôle d'accès, toute
 * validation serait refusée pour cette raison-là, et les cas testés ici
 * n'auraient plus l'occasion de se produire.
 *
 * @return array{0: Event, 1: Ticket}
 */
function eventWithTicket(?Organizer $organizer = null): array
{
    $organizer ??= Organizer::factory()->create();
    $event = Event::factory()->ongoing()->create(['organizer_id' => $organizer->id]);
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

/**
 * Le même billet, mais sur un événement placé où le test le demande.
 *
 * @return array{0: Event, 1: Ticket}
 */
function eventWithTicketBetween(string $start, string $end): array
{
    $event = Event::factory()->create([
        'organizer_id' => Organizer::factory()->create()->id,
        'start_date' => now()->parse($start),
        'end_date' => now()->parse($end),
    ]);

    $ticket = Ticket::factory()->create([
        'order_id' => Order::factory()->paid()->create()->id,
        'ticket_type_id' => TicketType::factory()->create(['event_id' => $event->id])->id,
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

/*
|--------------------------------------------------------------------------
| Fenêtre de contrôle d'accès
|--------------------------------------------------------------------------
|
| Un billet donne droit à une entrée à une date. Sans ces bornes, un billet du
| 7 août se consommait le 6, et son porteur se faisait refouler le jour dit.
|
*/

it('refuses a ticket presented before the check-in window opens', function (): void {
    [$event, $ticket] = eventWithTicketBetween('+3 days', '+3 days 4 hours');

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');

    // Le point essentiel : refusé *sans consommer*. Un billet brûlé la veille
    // ne se rend pas.
    expect($ticket->fresh()->checked_in_at)->toBeNull();
    $this->assertDatabaseCount('check_ins', 0);
});

it('refuses a ticket presented after the check-in window closed', function (): void {
    [$event, $ticket] = eventWithTicketBetween('-3 days', '-3 days +4 hours');

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');

    expect($ticket->fresh()->checked_in_at)->toBeNull();
});

it('lets the public in during the margin before the event starts', function (): void {
    // On fait entrer avant que ça commence : deux heures avant, sous la marge
    // par défaut de quatre.
    [$event, $ticket] = eventWithTicketBetween('+2 hours', '+5 hours');

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('still accepts a latecomer during the margin after the event ends', function (): void {
    [$event, $ticket] = eventWithTicketBetween('-5 hours', '-2 hours');

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('follows the margin set on the organizer profile', function (): void {
    // Un salon ouvre ses portes la veille : la valeur d'usine refuserait, la
    // façon de faire de l'organisateur doit suffire à l'autoriser — sans qu'il
    // ait à la répéter sur chaque événement.
    [$event, $ticket] = eventWithTicketBetween('+20 hours', '+30 hours');
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');

    $event->organizer->update(['checkin_open_hours_before' => 24]);

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('lets the event contradict its organizer', function (): void {
    // L'organisateur ouvre douze heures avant d'habitude ; cet événement-là est
    // particulier et n'ouvre qu'une heure avant. Le plus précis l'emporte.
    [$event, $ticket] = eventWithTicketBetween('+6 hours', '+9 hours');
    $event->organizer->update(['checkin_open_hours_before' => 12]);
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk();

    [$special, $specialTicket] = eventWithTicketBetween('+6 hours', '+9 hours');
    $special->organizer->update(['checkin_open_hours_before' => 12]);
    $special->update(['checkin_open_hours_before' => 1]);

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$special->id}/tickets/validate", ['code' => $specialTicket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');
});

it('says when the gate opens rather than just refusing', function (): void {
    // Au portique, « ouvre à 16:00 » évite l'appel à l'organisateur que
    // « refusé » déclenche.
    [$event, $ticket] = eventWithTicketBetween('+3 days', '+3 days 4 hours');

    $response = $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422);

    expect($response->json('message'))
        ->toContain('Trop tôt')
        ->toContain($event->start_date->copy()->subHours(4)->format('d/m/Y à H:i'));
});

it('lets an event override the factory margin', function (): void {
    // Une formation qui accueille dès 7 h du matin : la valeur d'usine de 4 h
    // refuserait, celle posée à la création de l'événement doit l'emporter.
    [$event, $ticket] = eventWithTicketBetween('+8 hours', '+16 hours');
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');

    $event->update(['checkin_open_hours_before' => 12]);

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('lets an event be stricter than the default', function (): void {
    // L'inverse : un événement qui n'ouvre qu'une heure avant, là où l'usine en
    // accorde quatre. La surcharge doit aussi savoir resserrer.
    [$event, $ticket] = eventWithTicketBetween('+2 hours', '+5 hours');
    $agent = staff('admin');

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk();

    [$strict, $strictTicket] = eventWithTicketBetween('+2 hours', '+5 hours');
    $strict->update(['checkin_open_hours_before' => 1]);

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$strict->id}/tickets/validate", ['code' => $strictTicket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');
});

it('keeps following its organizer when the event says nothing', function (): void {
    // Le cas de tous les événements existants : colonnes vides, donc
    // l'organisateur continue de les piloter — y compris quand il change
    // d'habitude après coup.
    [$event, $ticket] = eventWithTicketBetween('+20 hours', '+30 hours');
    $agent = staff('admin');

    expect($event->checkin_open_hours_before)->toBeNull();

    $event->organizer->update(['checkin_open_hours_before' => 24]);

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('treats a zero margin as a real value, not as "inherit"', function (): void {
    // Zéro doit fermer la porte à l'heure pile. Le confondre avec « hérite »
    // rendrait le réglage le plus strict impossible à exprimer.
    [$event, $ticket] = eventWithTicketBetween('+1 hour', '+4 hours');
    $event->update(['checkin_open_hours_before' => 0]);

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');
});

/*
|--------------------------------------------------------------------------
| Séances (occurrences)
|--------------------------------------------------------------------------
|
| Un événement peut se tenir à plusieurs dates, chacune vendue séparément. La
| fenêtre doit alors porter sur la séance du billet : sinon elle s'étend du
| premier au dernier jour, le billet du 9 passe le 7, et son porteur se fait
| refouler le 9 puisqu'un check-in ne se défait pas.
|
*/

/**
 * « Soirée Toofan » : l'événement court du 7 au 9, deux séances vendues à part.
 *
 * @return array{0: Event, 1: Ticket, 2: Ticket} l'événement, le billet du 7, celui du 9
 */
function eventWithTwoNights(): array
{
    $event = Event::factory()->create([
        'title' => 'Soirée Toofan',
        'organizer_id' => Organizer::factory()->create()->id,
        'start_date' => now()->addDays(2)->setTime(20, 0),
        'end_date' => now()->addDays(4)->setTime(23, 0),
    ]);

    $ticketFor = function (int $inDays) use ($event): Ticket {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $event->id,
            'start_date' => now()->addDays($inDays)->setTime(20, 0),
            'end_date' => now()->addDays($inDays)->setTime(23, 0),
        ]);

        return Ticket::factory()->create([
            'order_id' => Order::factory()->paid()->create()->id,
            'ticket_type_id' => TicketType::factory()->create([
                'event_id' => $event->id,
                'occurrence_id' => $occurrence->id,
            ])->id,
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
        ]);
    };

    return [$event, $ticketFor(2), $ticketFor(4)];
}

it('refuses a ticket for a later night presented at the first one', function (): void {
    // Le cœur du problème : les deux billets appartiennent au même événement,
    // dont la fenêtre couvre les deux soirs. Seule la séance les départage.
    [$event, $firstNight, $secondNight] = eventWithTwoNights();
    $agent = staff('admin');

    // On se place le soir de la première séance.
    $this->travelTo(now()->addDays(2)->setTime(19, 30));

    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $secondNight->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');

    // Refusé sans être consommé : il vaudra toujours pour son propre soir.
    expect($secondNight->fresh()->checked_in_at)->toBeNull();

    // Et celui du soir même passe.
    $this->actingAs($agent)
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $firstNight->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('accepts that same ticket on its own night', function (): void {
    [$event, , $secondNight] = eventWithTwoNights();

    $this->travelTo(now()->addDays(4)->setTime(19, 30));

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $secondNight->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('refuses a ticket for the first night once that night is over', function (): void {
    // L'événement court encore jusqu'au 9, mais la séance du 7 a fermé.
    [$event, $firstNight] = eventWithTwoNights();

    $this->travelTo(now()->addDays(3)->setTime(12, 0));

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $firstNight->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'outside_window');
});

it('names the night in the refusal, not just the event', function (): void {
    [$event, , $secondNight] = eventWithTwoNights();
    $expected = now()->addDays(4)->setTime(16, 0)->format('d/m/Y à H:i');

    $this->travelTo(now()->addDays(2)->setTime(19, 30));

    $response = $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $secondNight->qr_code])
        ->assertStatus(422);

    expect($response->json('message'))
        ->toContain('Trop tôt')
        ->toContain($expected);
});

it('leaves a ticket without a night on the event window', function (): void {
    // Le cas de la grande majorité du catalogue : pas de séance, donc rien ne
    // change — c'est la non-régression de tout ce qui précède.
    [$event, $ticket] = eventWithTicketBetween('+2 hours', '+5 hours');

    expect($ticket->ticketType->occurrence_id)->toBeNull();

    $this->actingAs(staff('admin'))
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertOk()
        ->assertJsonPath('data.result', 'ok');
});

it('exposes the gate state alongside the check-in history', function (): void {
    [$event] = eventWithTicketBetween('+3 days', '+3 days 4 hours');

    $this->actingAs(staff('admin'))
        ->getJson("/api/v1/events/{$event->id}/check-ins")
        ->assertOk()
        ->assertJsonPath('data.window.isOpen', false)
        ->assertJsonPath('data.window.reason', fn ($reason) => str_contains((string) $reason, 'Trop tôt'));
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
