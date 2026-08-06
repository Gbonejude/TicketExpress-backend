<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use App\Support\Commission;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function reportStaff(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role);
    $user->assignRole($role);
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/**
 * Une vente : un événement de l'organisateur, une commande payée à la date
 * demandée, et une ligne qui porte le montant.
 */
function sale(Organizer $organizer, float $amount, int $quantity, string $when): Event
{
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::PUBLISHED,
    ]);
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

    $order = Order::factory()->paid()->create([
        'total_amount' => $amount,
        'created_at' => now()->parse($when),
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => $quantity,
        'unit_price' => $amount / max($quantity, 1),
        'subtotal' => $amount,
    ]);

    return $event;
}

it('sums the revenue and derives the commission', function (): void {
    $organizer = Organizer::factory()->create();
    sale($organizer, 100_000, 2, 'now');
    sale($organizer, 50_000, 1, 'now');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=all')
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 150000)
        ->assertJsonPath(
            'data.totals.commission',
            fn ($value): bool => (float) $value === Commission::amountFor(150_000),
        )
        ->assertJsonPath(
            'data.totals.netRevenue',
            fn ($value): bool => (float) $value === Commission::netFor(150_000),
        )
        ->assertJsonPath('data.totals.ticketsSold', 3)
        ->assertJsonPath('data.totals.paidOrders', 2);
});

it('keeps only the orders inside the requested range', function (): void {
    $organizer = Organizer::factory()->create();
    sale($organizer, 100_000, 1, '2026-03-15');
    sale($organizer, 999_000, 1, '2026-06-15');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=range&startDate=2026-03-01&endDate=2026-03-31')
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 100000)
        ->assertJsonPath('data.period.type', 'range');
});

it('includes an order dated on the last day of the range', function (): void {
    // La borne haute est étendue à 23:59:59 : une commande de l'après-midi du
    // dernier jour doit compter.
    $organizer = Organizer::factory()->create();
    sale($organizer, 42_000, 1, '2026-03-31 16:30:00');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=range&startDate=2026-03-01&endDate=2026-03-31')
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 42000);
});

it('filters by month', function (): void {
    $organizer = Organizer::factory()->create();
    sale($organizer, 70_000, 1, '2026-08-10');
    sale($organizer, 30_000, 1, '2026-09-10');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=month&year=2026&month=8')
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 70000);
});

it('ignores unpaid orders in the revenue', function (): void {
    $organizer = Organizer::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
    $pending = Order::factory()->pending()->create(['total_amount' => 500_000]);

    OrderItem::factory()->create([
        'order_id' => $pending->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 1,
        'unit_price' => 500_000,
        'subtotal' => 500_000,
    ]);

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=all')
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 0)
        ->assertJsonPath('data.totals.orders', 1)
        ->assertJsonPath('data.totals.paidOrders', 0);
});

it('ranks events by revenue', function (): void {
    $organizer = Organizer::factory()->create();
    $small = sale($organizer, 10_000, 1, 'now');
    $big = sale($organizer, 900_000, 3, 'now');

    $response = $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/overview?filterType=all')
        ->assertOk();

    $top = $response->json('data.topEvents');

    expect($top[0]['eventId'])->toBe($big->id)
        ->and((float) $top[0]['revenue'])->toBe(900000.0)
        ->and($top[1]['eventId'])->toBe($small->id);
});

it('does not multiply an order that contains several ticket types', function (): void {
    // Le filtre organisateur passe par whereExists, pas par une jointure : sinon
    // une commande à deux lignes serait comptée deux fois.
    $organizer = Organizer::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $order = Order::factory()->paid()->create(['total_amount' => 60_000]);

    foreach ([20_000, 40_000] as $amount) {
        $ticketType = TicketType::factory()->create(['event_id' => $event->id]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 1,
            'unit_price' => $amount,
            'subtotal' => $amount,
        ]);
    }

    $this->actingAs(reportStaff('admin'))
        ->getJson("/api/v1/reports/overview?filterType=all&organizer_id={$organizer->id}")
        ->assertOk()
        ->assertJsonPath('data.totals.paidOrders', 1)
        ->assertJsonPath('data.totals.revenue', 60000);
});

it('confines an organizer to their own figures even when they ask for another', function (): void {
    $mine = Organizer::factory()->create();
    $manager = reportStaff('organizer-manager');
    $mine->update(['user_id' => $manager->id]);

    $other = Organizer::factory()->create();

    sale($mine, 100_000, 1, 'now');
    sale($other, 800_000, 1, 'now');

    $this->actingAs($manager)
        ->getJson("/api/v1/reports/overview?filterType=all&organizer_id={$other->id}")
        ->assertOk()
        ->assertJsonPath('data.totals.revenue', 100000);
});

it('counts the platform totals without a period', function (): void {
    $organizer = Organizer::factory()->create();

    // Un événement passé, un à venir, un annulé — et une vente, pour le
    // compteur de participants.
    Event::factory()->finished()->create(['organizer_id' => $organizer->id]);
    Event::factory()->create(['organizer_id' => $organizer->id]);
    Event::factory()->cancelled()->create(['organizer_id' => $organizer->id]);
    sale($organizer, 25_000, 1, 'now');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/platform')
        ->assertOk()
        ->assertJsonPath('data.organizers', 1)
        ->assertJsonPath('data.events', 4)
        ->assertJsonPath('data.cancelledEvents', 1)
        ->assertJsonPath('data.pastEvents', 1)
        ->assertJsonPath('data.upcomingEvents', 2)
        ->assertJsonPath('data.participants', 1);
});

it('keeps an annulled event out of the past and upcoming buckets', function (): void {
    // Un événement annulé dont la date est passée n'est pas « passé » : les
    // trois cases doivent rester disjointes.
    $organizer = Organizer::factory()->create();

    Event::factory()->cancelled()->create([
        'organizer_id' => $organizer->id,
        'start_date' => now()->subMonth(),
        'end_date' => now()->subMonth()->addHours(4),
    ]);

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/platform')
        ->assertOk()
        ->assertJsonPath('data.cancelledEvents', 1)
        ->assertJsonPath('data.pastEvents', 0)
        ->assertJsonPath('data.upcomingEvents', 0);
});

it('confines the platform totals of an organizer to their own events', function (): void {
    $mine = Organizer::factory()->create();
    $manager = reportStaff('organizer-manager');
    $mine->update(['user_id' => $manager->id]);

    $other = Organizer::factory()->create();

    Event::factory()->create(['organizer_id' => $mine->id]);
    Event::factory()->count(3)->create(['organizer_id' => $other->id]);

    $this->actingAs($manager)
        ->getJson('/api/v1/reports/platform')
        ->assertOk()
        ->assertJsonPath('data.events', 1)
        // Le compteur d'organisateurs n'a pas de sens à cette échelle.
        ->assertJsonPath('data.organizers', null);
});

it('returns the order journal for the period', function (): void {
    $organizer = Organizer::factory()->create();
    sale($organizer, 100_000, 2, '2026-03-15');
    sale($organizer, 999_000, 1, '2026-06-15');

    $this->actingAs(reportStaff('admin'))
        ->getJson('/api/v1/reports/journal?filterType=range&startDate=2026-03-01&endDate=2026-03-31')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.quantity', 2)
        ->assertJsonPath('data.0.totalAmount', 100000);
});

it('keeps the reports away from a participant', function (): void {
    $user = User::factory()->create();
    Role::findOrCreate('participant');
    $user->assignRole('participant');

    $this->actingAs($user)
        ->getJson('/api/v1/reports/overview?filterType=all')
        ->assertForbidden();
});
