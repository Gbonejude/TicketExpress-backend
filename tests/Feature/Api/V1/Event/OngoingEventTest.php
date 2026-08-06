<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function adminUser(): User
{
    $user = User::factory()->create();
    Role::findOrCreate('admin');
    $user->assignRole('admin');
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/** Un événement publié sur l'intervalle demandé, avec des billets. */
function eventBetween(
    string $start,
    ?string $end,
    int $sold = 0,
    int $scanned = 0,
    ?Organizer $organizer = null,
): Event {
    $event = Event::factory()->create([
        'organizer_id' => ($organizer ?? Organizer::factory()->create())->id,
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->parse($start),
        'end_date' => $end === null ? null : now()->parse($end),
    ]);

    if ($sold > 0) {
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
        $order = Order::factory()->paid()->create();

        for ($i = 0; $i < $sold; $i++) {
            Ticket::factory()->create([
                'order_id' => $order->id,
                'ticket_type_id' => $ticketType->id,
                'status' => TicketStatus::VALID,
                'checked_in_at' => $i < $scanned ? now() : null,
            ]);
        }
    }

    return $event;
}

it('lists only the events happening right now', function (): void {
    $running = eventBetween('-1 hour', '+2 hours');
    eventBetween('+3 days', '+3 days 4 hours');   // à venir
    eventBetween('-3 days', '-2 days');           // terminé

    $this->actingAs(adminUser())
        ->getJson('/api/v1/events/ongoing')
        ->assertOk()
        ->assertJsonCount(1, 'data.ongoing')
        ->assertJsonPath('data.ongoing.0.id', $running->id)
        ->assertJsonPath('data.totals.ongoing', 1);
});

// Pas de cas « événement sans date de fin » : `events.end_date` est NOT NULL en
// base, l'état est donc inatteignable. Le `orWhereNull` du contrôleur n'est là
// que par symétrie avec le filtre `when=upcoming` de la liste publique.

it('leaves out drafts and cancelled events whose dates span now', function (): void {
    Event::factory()->create([
        'status' => EventStatus::DRAFT,
        'start_date' => now()->subHour(),
        'end_date' => now()->addHour(),
    ]);
    Event::factory()->create([
        'status' => EventStatus::CANCELLED,
        'start_date' => now()->subHour(),
        'end_date' => now()->addHour(),
    ]);

    $this->actingAs(adminUser())
        ->getJson('/api/v1/events/ongoing')
        ->assertOk()
        ->assertJsonCount(0, 'data.ongoing');
});

it('counts sold and scanned tickets per event', function (): void {
    eventBetween('-1 hour', '+2 hours', sold: 5, scanned: 3);

    $this->actingAs(adminUser())
        ->getJson('/api/v1/events/ongoing')
        ->assertOk()
        ->assertJsonPath('data.ongoing.0.sold', 5)
        ->assertJsonPath('data.ongoing.0.scanned', 3)
        ->assertJsonPath('data.ongoing.0.notScanned', 2)
        ->assertJsonPath('data.ongoing.0.attendanceRate', 60)
        ->assertJsonPath('data.totals.sold', 5)
        ->assertJsonPath('data.totals.scanned', 3);
});

it('returns the events starting inside the soon window', function (): void {
    $soon = eventBetween('+3 hours', '+6 hours');

    eventBetween('+5 days', '+5 days 2 hours');

    $this->actingAs(adminUser())
        ->getJson('/api/v1/events/ongoing?soon_hours=24')
        ->assertOk()
        ->assertJsonCount(1, 'data.soon')
        ->assertJsonPath('data.soon.0.id', $soon->id)
        ->assertJsonPath('data.soonHours', 24);
});

it('shows an organizer only their own ongoing events', function (): void {
    $mine = Organizer::factory()->create();
    $manager = User::factory()->create();

    Role::findOrCreate('organizer-manager');
    $manager->assignRole('organizer-manager');
    $mine->update(['user_id' => $manager->id]);
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $own = eventBetween('-1 hour', '+2 hours', organizer: $mine);
    eventBetween('-1 hour', '+2 hours');

    $this->actingAs($manager)
        ->getJson('/api/v1/events/ongoing')
        ->assertOk()
        ->assertJsonCount(1, 'data.ongoing')
        ->assertJsonPath('data.ongoing.0.id', $own->id);
});

it('keeps the ongoing screen away from a participant', function (): void {
    $user = User::factory()->create();
    Role::findOrCreate('participant');
    $user->assignRole('participant');

    $this->actingAs($user)
        ->getJson('/api/v1/events/ongoing')
        ->assertForbidden();
});
