<?php

declare(strict_types=1);

use App\Enums\AvailabilityStatus;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Le côté public ne montre pas les événements terminés — et l'URL directe non
 * plus.
 *
 * Trois publics à distinguer, et c'est tout l'objet de ces tests : le visiteur
 * anonyme, le participant connecté, et l'exploitation. La règle a longtemps été
 * écrite en fonction de « connecté ou pas », ce qui rangeait le participant du
 * côté de l'administration ; et comme les routes du catalogue sont publiques,
 * `$request->user()` répondait `null` même à un administrateur muni de son
 * jeton. Les deux erreurs se compensaient jusqu'à ce qu'on masque quelque chose.
 */
beforeEach(function (): void {
    $this->seed(RoleSeeder::class);

    $this->organizer = Organizer::factory()->create(['is_active' => true]);

    $this->past = Event::factory()->for($this->organizer)->finished()->create([
        'title' => 'Concert de l’an dernier',
    ]);

    $this->upcoming = Event::factory()->for($this->organizer)->create([
        'title' => 'Concert à venir',
        'status' => EventStatus::PUBLISHED,
    ]);
});

function participant(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::PARTICIPANT->value);

    return $user;
}

function backOffice(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::ADMIN->value);

    return $user;
}

it('keeps past events out of the public catalogue', function (): void {
    $titles = collect($this->getJson('/api/v1/events')->assertOk()->json('data'))
        ->pluck('title');

    expect($titles)->toContain('Concert à venir')
        ->and($titles)->not->toContain('Concert de l’an dernier');
});

it('keeps them out for a signed-in participant too', function (): void {
    $titles = collect(
        $this->actingAs(participant(), 'sanctum')
            ->getJson('/api/v1/events')
            ->assertOk()
            ->json('data'),
    )->pluck('title');

    expect($titles)->not->toContain('Concert de l’an dernier');
});

it('answers nothing when the public asks for past events explicitly', function (): void {
    expect($this->getJson('/api/v1/events?when=past')->assertOk()->json('data'))->toBeEmpty();
});

it('still lists them for the back-office', function (): void {
    $titles = collect(
        $this->actingAs(backOffice(), 'sanctum')
            ->getJson('/api/v1/events?when=past')
            ->assertOk()
            ->json('data'),
    )->pluck('title');

    expect($titles)->toContain('Concert de l’an dernier');
});

it('does not serve a past event by its own URL', function (): void {
    $this->getJson("/api/v1/events/{$this->past->id}")->assertNotFound();
});

it('does not serve it to a participant who has the URL either', function (): void {
    $this->actingAs(participant(), 'sanctum')
        ->getJson("/api/v1/events/{$this->past->id}")
        ->assertNotFound();
});

it('serves it to the back-office by URL', function (): void {
    $this->actingAs(backOffice(), 'sanctum')
        ->getJson("/api/v1/events/{$this->past->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Concert de l’an dernier');
});

it('keeps serving an event that has started but not finished', function (): void {
    $ongoing = Event::factory()->for($this->organizer)->ongoing()->create();

    $this->getJson("/api/v1/events/{$ongoing->id}")->assertOk();
});

it('hides an event whose organizer was deactivated, URL included', function (): void {
    $suspended = Organizer::factory()->create(['is_active' => false]);
    $event = Event::factory()->for($suspended)->create(['status' => EventStatus::PUBLISHED]);

    $this->getJson("/api/v1/events/{$event->id}")->assertNotFound();
    $this->actingAs(backOffice(), 'sanctum')
        ->getJson("/api/v1/events/{$event->id}")
        ->assertOk();
});

it('does not serve a participant the page cached for the back-office', function (): void {
    // L'ordre compte : c'est le back-office qui remplit le cache en premier. Avec
    // une clé commune à tous les comptes connectés, la requête du participant qui
    // suit recevait la réponse mise en cache — donc l'événement masqué.
    $this->actingAs(backOffice(), 'sanctum')
        ->getJson("/api/v1/events/{$this->past->id}")
        ->assertOk();

    $this->actingAs(participant(), 'sanctum')
        ->getJson("/api/v1/events/{$this->past->id}")
        ->assertNotFound();
});

it('closes the per-event public reads too', function (): void {
    // La fiche est fermée, mais ces deux routes publiques rendaient encore les
    // tarifs et les dates de représentation. Une porte de service reste une
    // porte.
    $this->getJson("/api/v1/events/{$this->past->id}/ticket-types")->assertNotFound();
    $this->getJson("/api/v1/events/{$this->past->id}/occurrences")->assertNotFound();

    $this->getJson("/api/v1/events/{$this->upcoming->id}/ticket-types")->assertOk();
    $this->getJson("/api/v1/events/{$this->upcoming->id}/occurrences")->assertOk();
});

it('leaves those reads open to the back-office', function (): void {
    $this->actingAs(backOffice(), 'sanctum')
        ->getJson("/api/v1/events/{$this->past->id}/ticket-types")
        ->assertOk();
});

it('closes the sale on a past event’s ticket types', function (): void {
    $tier = TicketType::factory()->for($this->past, 'event')->create([
        'sale_start_date' => now()->subMonths(3),
        'sale_end_date' => $this->past->start_date,
        'quantity' => 100,
        'sold_quantity' => 10,
    ]);

    expect($tier->availabilityStatus())->toBe(AvailabilityStatus::SALE_CLOSED)
        ->and($tier->availabilityStatus()->isAvailableForPurchase())->toBeFalse();
});

it('marks a tier whose sale has not opened yet', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'sale_start_date' => now()->addWeek(),
        'sale_end_date' => now()->addMonth(),
        'quantity' => 100,
        'sold_quantity' => 0,
    ]);

    expect($tier->availabilityStatus())->toBe(AvailabilityStatus::SALE_NOT_STARTED)
        ->and($tier->availabilityStatus()->isAvailableForPurchase())->toBeFalse();
});

it('leaves a tier without sale dates on sale', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'sale_start_date' => null,
        'sale_end_date' => null,
        'quantity' => 100,
        'sold_quantity' => 0,
    ]);

    expect($tier->availabilityStatus()->isAvailableForPurchase())->toBeTrue();
});

it('refuses an order on a past event', function (): void {
    $tier = TicketType::factory()->for($this->past, 'event')->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        // Sans dates de vente : c'est le cas des tarifs créés avant que ces
        // colonnes ne soient renseignées, et celui où seule la date de
        // l'événement peut trancher.
        'sale_start_date' => null,
        'sale_end_date' => null,
    ]);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 1]],
        ])
        ->assertStatus(422)
        ->assertJsonPath('success', false);

    // Le stock ne doit pas avoir bougé : la transaction est annulée en entier.
    expect($tier->fresh()->sold_quantity)->toBe(0);
});

it('refuses an order whose sale window has closed on an upcoming event', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'sale_start_date' => now()->subMonth(),
        'sale_end_date' => now()->subDay(),
    ]);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 1]],
        ])
        ->assertStatus(422);

    expect($tier->fresh()->sold_quantity)->toBe(0);
});

it('still accepts an order on an event that is on sale', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'quantity' => 100,
        'sold_quantity' => 0,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 2]],
        ])
        ->assertCreated();

    expect($tier->fresh()->sold_quantity)->toBe(2);
});

it('charges the promotional price, not the full one', function (): void {
    // La caisse lisait `price` pendant que la fiche affichait `currentPrice()` :
    // le client voyait 7 500 F et était débité 10 000 F.
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'price' => 10000,
        'promotional_price' => 7500,
        'promotion_start_date' => now()->subDay(),
        'promotion_end_date' => now()->addWeek(),
        'quantity' => 100,
        'sold_quantity' => 0,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    $response = $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 2]],
        ])
        ->assertCreated();

    expect((float) $response->json('data.totalAmount'))->toBe(15000.0);
});

it('charges the full price once the promotion window has closed', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'price' => 10000,
        'promotional_price' => 7500,
        'promotion_start_date' => now()->subMonth(),
        'promotion_end_date' => now()->subDay(),
        'quantity' => 100,
        'sold_quantity' => 0,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    $response = $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 1]],
        ])
        ->assertCreated();

    expect((float) $response->json('data.totalAmount'))->toBe(10000.0);
});

it('refuses to sell a tier that is sold out', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'quantity' => 10,
        'sold_quantity' => 10,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    expect($tier->availabilityStatus())->toBe(AvailabilityStatus::SOLD_OUT);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 1]],
        ])
        ->assertStatus(422);

    expect($tier->fresh()->sold_quantity)->toBe(10);
});

it('refuses to sell more than what is left', function (): void {
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'quantity' => 10,
        'sold_quantity' => 8,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [['ticket_type_id' => $tier->id, 'quantity' => 3]],
        ])
        ->assertStatus(422);

    // Rien n'est consommé : la transaction est annulée en entier.
    expect($tier->fresh()->sold_quantity)->toBe(8);
});

it('counts two lines on the same tier against one stock', function (): void {
    // Deux lignes qui visent le même tarif : chacune est validée contre le stock
    // déjà entamé par la précédente, sinon on vendait deux fois les mêmes places.
    $tier = TicketType::factory()->for($this->upcoming, 'event')->create([
        'quantity' => 10,
        'sold_quantity' => 8,
        'sale_start_date' => now()->subWeek(),
        'sale_end_date' => $this->upcoming->start_date,
    ]);

    $this->actingAs(participant(), 'sanctum')
        ->postJson('/api/v1/orders', [
            'first_name' => 'Komi',
            'last_name' => 'Creppy',
            'email' => 'komi@example.tg',
            'phone' => '+22890510465',
            'delivery_method' => 'email',
            'items' => [
                ['ticket_type_id' => $tier->id, 'quantity' => 2],
                ['ticket_type_id' => $tier->id, 'quantity' => 1],
            ],
        ])
        ->assertStatus(422);

    expect($tier->fresh()->sold_quantity)->toBe(8);
});

it('drops past events from a participant’s favourites', function (): void {
    $user = participant();
    $user->favoriteEvents()->attach([$this->past->id, $this->upcoming->id]);

    $titles = collect(
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/favorites')->assertOk()->json('data'),
    )->pluck('title');

    expect($titles)->toContain('Concert à venir')
        ->and($titles)->not->toContain('Concert de l’an dernier')
        // Le rattachement reste : masquer n'est pas supprimer.
        ->and($user->favoriteEvents()->count())->toBe(2);
});
