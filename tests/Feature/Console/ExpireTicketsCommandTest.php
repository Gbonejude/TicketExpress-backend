<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/** Un administrateur, pour le test qui remonte jusqu'au portique. */
function expiryTestAdmin(): User
{
    $user = User::factory()->create();
    Role::findOrCreate('admin');
    $user->assignRole('admin');

    return $user;
}

/**
 * Un billet rattaché à un événement placé où le test le demande.
 */
function expirableTicket(string $start, string $end, TicketStatus $status = TicketStatus::VALID, ?string $checkedInAt = null): Ticket
{
    $event = Event::factory()->create([
        'start_date' => now()->parse($start),
        'end_date' => now()->parse($end),
    ]);

    return Ticket::factory()->create([
        'order_id' => Order::factory()->paid()->create()->id,
        'ticket_type_id' => TicketType::factory()->create(['event_id' => $event->id])->id,
        'status' => $status,
        'checked_in_at' => $checkedInAt === null ? null : now()->parse($checkedInAt),
    ]);
}

it('expires a valid ticket whose event is long over', function (): void {
    $ticket = expirableTicket('-3 days', '-3 days +4 hours');

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::EXPIRED);
});

it('leaves a ticket alone while the gate is still open', function (): void {
    // Terminé il y a deux heures : encore dans la tolérance de quatre heures,
    // un retardataire peut toujours entrer.
    $ticket = expirableTicket('-5 hours', '-2 hours');

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::VALID);
});

it('leaves future events untouched', function (): void {
    $ticket = expirableTicket('+3 days', '+3 days 4 hours');

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::VALID);
});

it('keeps the history of a ticket that was actually used', function (): void {
    // Entré à l'événement : le billet garde son statut et sa trace, il n'a rien
    // d'une place perdue.
    $ticket = expirableTicket('-3 days', '-3 days +4 hours', TicketStatus::VALID, '-3 days +1 hour');

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::VALID)
        ->and($ticket->fresh()->checked_in_at)->not->toBeNull();
});

it('does not overwrite a refund or a cancellation', function (): void {
    // Ces statuts portent une décision — remboursement, annulation. Les écraser
    // par « expiré » effacerait pourquoi le billet ne vaut plus rien.
    $refunded = expirableTicket('-3 days', '-3 days +4 hours', TicketStatus::REFUNDED);
    $cancelled = expirableTicket('-3 days', '-3 days +4 hours', TicketStatus::CANCELLED);

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($refunded->fresh()->status)->toBe(TicketStatus::REFUNDED)
        ->and($cancelled->fresh()->status)->toBe(TicketStatus::CANCELLED);
});

it('changes nothing on a dry run', function (): void {
    $ticket = expirableTicket('-3 days', '-3 days +4 hours');

    $this->artisan('tickets:expire', ['--dry-run' => true])->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::VALID);
});

it('follows the closing margin set on the organizer', function (): void {
    // Terminé il y a six heures : périmé sous la valeur d'usine de quatre…
    $ticket = expirableTicket('-9 hours', '-6 hours');
    $organizer = $ticket->ticketType->event->organizer;

    $organizer->update(['checkin_close_hours_after' => 12]);

    // …mais toujours valide si l'organisateur s'accorde douze heures.
    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::VALID);

    $organizer->update(['checkin_close_hours_after' => 4]);

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($ticket->fresh()->status)->toBe(TicketStatus::EXPIRED);
});

it('honours each event own closing margin', function (): void {
    // Deux événements terminés à la même heure, deux tolérances différentes :
    // un seuil global unique en périmerait un à tort. Le second est aussi ce
    // qui garantit que le préfiltre SQL ne coupe pas trop court.
    $strict = expirableTicket('-9 hours', '-6 hours');
    $lenient = expirableTicket('-9 hours', '-6 hours');

    $strict->ticketType->event->update(['checkin_close_hours_after' => 2]);
    $lenient->ticketType->event->update(['checkin_close_hours_after' => 24]);

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($strict->fresh()->status)->toBe(TicketStatus::EXPIRED)
        ->and($lenient->fresh()->status)->toBe(TicketStatus::VALID);
});

it('does not let a generous event hide a strict one from the sweep', function (): void {
    // Le préfiltre prend la marge la plus large en circulation. Si un événement
    // très permissif existe, il élargit la maille — les autres doivent quand
    // même être jugés sur la leur.
    expirableTicket('-2 days', '-2 days +3 hours')->ticketType->event
        ->update(['checkin_close_hours_after' => 168]);

    $normal = expirableTicket('-3 days', '-3 days +4 hours');

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($normal->fresh()->status)->toBe(TicketStatus::EXPIRED);
});

it('expires a past night while its event is still running', function (): void {
    // Un événement du 7 au 9, deux séances. Le 8, celle du 7 a fermé — ses
    // billets sont perdus — mais celle du 9 est encore à venir. Un balayage
    // borné sur la fin de l'événement ne verrait ni l'une ni l'autre.
    // Horloge figée au « 8 à midi » : sans ça, la séance de la veille tombe
    // encore dans la marge de quatre heures quand la suite tourne la nuit.
    $this->travelTo(now()->addDay()->setTime(12, 0));

    $event = Event::factory()->create([
        'start_date' => now()->subDay()->setTime(20, 0),
        'end_date' => now()->addDay()->setTime(23, 0),
    ]);

    $ticketForNight = function (int $dayOffset) use ($event): Ticket {
        $occurrence = EventOccurrence::factory()->create([
            'event_id' => $event->id,
            'start_date' => now()->addDays($dayOffset)->setTime(20, 0),
            'end_date' => now()->addDays($dayOffset)->setTime(23, 0),
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

    $past = $ticketForNight(-1);      // la séance de la veille, fermée depuis 3 h du matin
    $upcoming = $ticketForNight(+1);  // celle du lendemain, pas encore ouverte

    $this->artisan('tickets:expire')->assertSuccessful();

    expect($past->fresh()->status)->toBe(TicketStatus::EXPIRED)
        ->and($upcoming->fresh()->status)->toBe(TicketStatus::VALID);
});

it('refuses an expired ticket at the gate, saying it is expired', function (): void {
    // Le cas où le statut compte vraiment. La fenêtre refuserait déjà ce billet
    // — même motif pour tout le monde. Ici l'organisateur a élargi sa tolérance
    // après le passage de la commande : le portique rouvre, et c'est le statut
    // du billet, seul, qui le refuse. Le motif doit le dire.
    $ticket = expirableTicket('-9 hours', '-6 hours');

    $this->artisan('tickets:expire')->assertSuccessful();
    expect($ticket->fresh()->status)->toBe(TicketStatus::EXPIRED);

    $event = $ticket->ticketType->event;
    $event->organizer->update(['checkin_close_hours_after' => 12]);

    $response = $this->actingAs(expiryTestAdmin())
        ->postJson("/api/v1/events/{$event->id}/tickets/validate", ['code' => $ticket->qr_code])
        ->assertStatus(422)
        ->assertJsonPath('errors.result', 'not_valid');

    expect($response->json('message'))->toContain('expiré');

    expect($ticket->fresh()->checked_in_at)->toBeNull();
});
