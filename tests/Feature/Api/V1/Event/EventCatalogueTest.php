<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Filters used by the public catalogue (search page, city filter, sorting).
 *
 * These run unauthenticated on purpose: this is the visitor's view of
 * `GET /events`, which also hides events of deactivated organizers.
 */
beforeEach(function (): void {
    // The company name is searchable too, so it is pinned rather than left to
    // faker — otherwise a generated name containing a search term would make
    // every assertion below fail at random.
    $this->organizer = Organizer::factory()->create([
        'is_active' => true,
        'company_name' => 'Organisateur de test',
    ]);
});

it('matches the search term against the title', function (): void {
    Event::factory()->for($this->organizer)->create([
        'title' => 'Concert Afrobeat',
        'description' => 'Une soirée musicale.',
        'status' => EventStatus::PUBLISHED,
    ]);
    Event::factory()->for($this->organizer)->create([
        'title' => 'Conférence Tech',
        'description' => 'Une journée de conférences.',
        'status' => EventStatus::PUBLISHED,
    ]);

    $response = $this->getJson('/api/v1/events?search=Afrobeat')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Concert Afrobeat');
});

it('matches the search term against the venue city', function (): void {
    // Every searchable field of the control event is pinned. The factories fill
    // titles, descriptions and venue names with faker text, which can itself
    // contain the search term and make this assertion fail at random.
    $lome = Venue::factory()->create(['city' => 'Lomé', 'name' => 'Palais des congrès']);
    $kara = Venue::factory()->create(['city' => 'Kara', 'name' => 'Stade municipal']);

    Event::factory()->for($this->organizer)->for($lome)->create([
        'title' => 'Gala',
        'description' => 'Soirée de gala annuelle.',
    ]);
    Event::factory()->for($this->organizer)->for($kara)->create([
        'title' => 'Tournoi',
        'description' => 'Compétition sportive régionale.',
    ]);

    $response = $this->getJson('/api/v1/events?search=Kara')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Tournoi');
});

it('filters by city', function (): void {
    $lome = Venue::factory()->create(['city' => 'Lomé']);
    $kara = Venue::factory()->create(['city' => 'Kara']);

    Event::factory()->for($this->organizer)->for($lome)->create();
    Event::factory()->for($this->organizer)->for($kara)->create();

    $response = $this->getJson('/api/v1/events?city=Kara')->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('splits upcoming from past events on the end date', function (): void {
    Event::factory()->for($this->organizer)->create([
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
        'title' => 'À venir',
    ]);
    Event::factory()->for($this->organizer)->create([
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(9),
        'title' => 'Passé',
    ]);

    $upcoming = $this->getJson('/api/v1/events?when=upcoming')->assertOk();
    $past = $this->getJson('/api/v1/events?when=past')->assertOk();

    expect($upcoming->json('data'))->toHaveCount(1)
        ->and($upcoming->json('data.0.title'))->toBe('À venir')
        ->and($past->json('data'))->toHaveCount(1)
        ->and($past->json('data.0.title'))->toBe('Passé');
});

it('sorts by the cheapest ticket type', function (): void {
    $cheap = Event::factory()->for($this->organizer)->create(['title' => 'Abordable']);
    $pricey = Event::factory()->for($this->organizer)->create(['title' => 'Premium']);

    TicketType::factory()->for($cheap, 'event')->create(['price' => 2000, 'promotional_price' => null]);
    TicketType::factory()->for($pricey, 'event')->create(['price' => 90000, 'promotional_price' => null]);

    $asc = $this->getJson('/api/v1/events?sort=price-asc')->assertOk();
    $desc = $this->getJson('/api/v1/events?sort=price-desc')->assertOk();

    expect($asc->json('data.0.title'))->toBe('Abordable')
        ->and($desc->json('data.0.title'))->toBe('Premium');
});

it('filters on a start-date window', function (): void {
    Event::factory()->for($this->organizer)->create([
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(3)->addHours(2),
        'title' => 'Dans la fenêtre',
    ]);
    Event::factory()->for($this->organizer)->create([
        'start_date' => now()->addDays(40),
        'end_date' => now()->addDays(40)->addHours(2),
        'title' => 'Trop tard',
    ]);

    $after = now()->toDateString();
    $before = now()->addDays(7)->toDateString();

    $response = $this->getJson("/api/v1/events?starts_after={$after}&starts_before={$before}")
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Dans la fenêtre');
});

it('filters on a price ceiling using the effective price', function (): void {
    $cheap = Event::factory()->for($this->organizer)->create(['title' => 'Abordable']);
    $pricey = Event::factory()->for($this->organizer)->create(['title' => 'Premium']);
    $discounted = Event::factory()->for($this->organizer)->create(['title' => 'En promo']);

    TicketType::factory()->for($cheap, 'event')->create(['price' => 5000, 'promotional_price' => null]);
    TicketType::factory()->for($pricey, 'event')->create(['price' => 80000, 'promotional_price' => null]);

    // Listed above the ceiling, but on promotion below it — must be kept.
    TicketType::factory()->for($discounted, 'event')->create([
        'price' => 80000,
        'promotional_price' => 9000,
        'promotion_start_date' => now()->subDay(),
        'promotion_end_date' => now()->addDay(),
    ]);

    $response = $this->getJson('/api/v1/events?max_price=10000')->assertOk();

    $titles = collect($response->json('data'))->pluck('title')->all();

    expect($titles)->toHaveCount(2)
        ->and($titles)->toContain('Abordable')
        ->and($titles)->toContain('En promo');
});

it('returns the ticket types with each event', function (): void {
    // The cards derive "à partir de X" and the sold-out state from these. When
    // the listing omitted them every card read "Indisponible" and "Complet",
    // because `[].every()` is true.
    $event = Event::factory()->for($this->organizer)->create();

    TicketType::factory()->for($event, 'event')->create(['name' => 'Standard', 'price' => 5000]);
    TicketType::factory()->for($event, 'event')->create(['name' => 'VIP', 'price' => 25000]);

    $response = $this->getJson('/api/v1/events')->assertOk();

    expect($response->json('data.0.ticketTypes'))->toHaveCount(2)
        ->and(collect($response->json('data.0.ticketTypes'))->pluck('currentPrice')->min())
        ->toEqual(5000);
});

it('matches the search term against the category', function (): void {
    $concerts = EventCategory::factory()->create(['name' => 'Concert', 'slug' => 'concert']);
    $sport = EventCategory::factory()->create(['name' => 'Sport', 'slug' => 'sport']);

    Event::factory()->for($this->organizer)->for($concerts, 'category')->create([
        'title' => 'Soirée live',
        'description' => 'Une soirée.',
    ]);
    Event::factory()->for($this->organizer)->for($sport, 'category')->create([
        'title' => 'Tournoi',
        'description' => 'Une compétition.',
    ]);

    $response = $this->getJson('/api/v1/events?search=Concert')->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.title'))->toBe('Soirée live');
});

it('filters by event type', function (): void {
    Event::factory()->for($this->organizer)->create(['event_type' => 'online', 'title' => 'Webinaire']);
    Event::factory()->for($this->organizer)->create(['event_type' => 'physical', 'title' => 'Sur place']);

    $online = $this->getJson('/api/v1/events?event_type=online')->assertOk();

    expect($online->json('data'))->toHaveCount(1)
        ->and($online->json('data.0.title'))->toBe('Webinaire');
});

it('honours per_page and caps it at 50', function (): void {
    Event::factory()->for($this->organizer)->count(12)->create();

    $page = $this->getJson('/api/v1/events?per_page=5')->assertOk();

    expect($page->json('data'))->toHaveCount(5)
        ->and($page->json('meta.per_page'))->toBe(5);

    $capped = $this->getJson('/api/v1/events?per_page=999')->assertOk();

    expect($capped->json('meta.per_page'))->toBe(50);
});
