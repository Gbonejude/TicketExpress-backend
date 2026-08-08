<?php

declare(strict_types=1);

use App\Enums\AvailabilityStatus;
use App\Enums\OrderStatus;
use App\Enums\OrganizerStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventOccurrence;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Venue;
use Database\Seeders\PlatformDemoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The demo catalogue is what every screen is judged on, so it is worth a test:
 * a seeder that half-runs leaves the site looking broken in a way that reads as
 * a front-end bug.
 */
beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(PlatformDemoSeeder::class);
});

it('seeds the whole catalogue', function (): void {
    expect(EventCategory::count())->toBeGreaterThanOrEqual(12)
        ->and(Venue::count())->toBeGreaterThanOrEqual(10)
        ->and(Organizer::count())->toBeGreaterThanOrEqual(6)
        ->and(Event::count())->toBeGreaterThanOrEqual(20);
});

it('gives every event a category, an organizer and ticket types', function (): void {
    Event::query()->with('ticketTypes')->get()->each(function (Event $event): void {
        expect($event->category_id)->not->toBeNull()
            ->and($event->organizer_id)->not->toBeNull()
            ->and($event->ticketTypes)->not->toBeEmpty();
    });
});

it('gives every event a banner image', function (): void {
    Event::query()->with('media')->get()->each(function (Event $event): void {
        expect($event->banner)->not->toBeNull();
    });
});

// Borné aux organisateurs approuvés : ce sont eux qui paraissent sur le site, et
// c'est leur logo manquant qui se voit. Un dossier encore à l'étude n'est pas
// tenu d'avoir déposé son visuel — le seeder en laisse justement un sans, pour
// que l'écran d'approbation montre aussi ce cas.
it('gives every approved organizer a logo and a Togolese contact', function (): void {
    $approved = Organizer::query()
        ->where('status', OrganizerStatus::APPROVED)
        ->with(['media', 'user'])
        ->get();

    expect($approved)->not->toBeEmpty();

    $approved->each(function (Organizer $organizer): void {
        expect($organizer->logo)->not->toBeNull()
            ->and($organizer->user)->not->toBeNull()
            ->and($organizer->user->phone)->toStartWith('+228');
    });
});

it('leaves organizer applications waiting for a decision', function (): void {
    expect(Organizer::where('status', OrganizerStatus::PENDING)->count())->toBeGreaterThan(0)
        ->and(Organizer::where('status', OrganizerStatus::REJECTED)->count())->toBeGreaterThan(0);

    // Un refus sans motif n'est pas opposable : c'est la seule chose qu'on peut
    // rendre au demandeur pour qu'il corrige son dossier.
    Organizer::where('status', OrganizerStatus::REJECTED)->get()->each(function (Organizer $organizer): void {
        expect($organizer->rejection_reason)->not->toBeNull();
    });
});

it('geolocates every physical venue', function (): void {
    Venue::all()->each(function (Venue $venue): void {
        expect($venue->latitude)->not->toBeNull()
            ->and($venue->longitude)->not->toBeNull()
            ->and($venue->country)->toBe('Togo');
    });
});

it('creates recurring events with several occurrences', function (): void {
    expect(EventOccurrence::count())->toBeGreaterThan(0);

    $recurring = Event::query()->has('occurrences')->withCount('occurrences')->get();

    expect($recurring)->not->toBeEmpty();
    $recurring->each(fn (Event $event) => expect($event->occurrences_count)->toBeGreaterThan(1));
});

it('mixes online and physical events', function (): void {
    expect(Event::where('event_type', 'online')->count())->toBeGreaterThan(0)
        ->and(Event::where('event_type', 'physical')->count())->toBeGreaterThan(0);
});

it('mixes upcoming and past events', function (): void {
    expect(Event::where('end_date', '>=', now())->count())->toBeGreaterThan(0)
        ->and(Event::where('end_date', '<', now())->count())->toBeGreaterThan(0);
});

it('gives every category at least two events', function (): void {
    // A category tile that leads to a single card makes the filter look broken.
    EventCategory::query()->withCount('events')->get()->each(function (EventCategory $category): void {
        expect($category->events_count)
            ->toBeGreaterThanOrEqual(2, "La catégorie « {$category->name} » n'a que {$category->events_count} événement(s).");
    });
});

it('puts at least five events on promotion so the badge is visible', function (): void {
    $onPromotion = Event::query()
        ->whereHas('ticketTypes', fn ($q) => $q->whereNotNull('promotional_price'))
        ->count();

    expect($onPromotion)->toBeGreaterThanOrEqual(5);
});

it('makes those promotions currently active', function (): void {
    // A promotional price outside its window is ignored by `currentPrice()`,
    // so the badge would never show however many rows carried one.
    $active = TicketType::query()
        ->whereNotNull('promotional_price')
        ->where(fn ($q) => $q->whereNull('promotion_start_date')->orWhere('promotion_start_date', '<=', now()))
        ->where(fn ($q) => $q->whereNull('promotion_end_date')->orWhere('promotion_end_date', '>=', now()))
        ->count();

    expect($active)->toBeGreaterThanOrEqual(5);
});

it('puts every visible event on sale, ongoing ones included', function (): void {
    // « Vente fermée » ou « Bientôt en vente » ne doit apparaître sur aucun
    // événement que le site propose. Deux réglages le provoquaient : une
    // fermeture calée sur le *début* de l'événement — ce qui fermait les
    // événements en cours, alors qu'un retardataire doit pouvoir acheter — et une
    // ouverture huit semaines avant, encore dans le futur pour un événement
    // lointain. Seul l'épuisement du stock a le droit de bloquer une vente.
    $blocked = Event::query()
        ->with('ticketTypes')
        ->where('end_date', '>=', now())
        ->get()
        ->flatMap(fn (Event $event) => $event->ticketTypes->map(
            fn (TicketType $tier) => ['event' => $event->title, 'tier' => $tier->name, 'status' => $tier->availabilityStatus()],
        ))
        ->filter(fn (array $row) => in_array(
            $row['status'],
            [AvailabilityStatus::SALE_CLOSED, AvailabilityStatus::SALE_NOT_STARTED],
            true,
        ));

    expect($blocked)->toBeEmpty();
});

it('leaves at least three events completely sold out, and one tier of another', function (): void {
    // Le tirage aléatoire des ventes ne monte jamais à 100 %, donc « Complet »
    // n'apparaissait nulle part : ni sur une carte du catalogue, ni sur le bouton
    // d'une fiche, ni au refus de la caisse. Les cas sont posés à la main.
    //
    // Trois, et dans trois catégories différentes : un seul événement complet se
    // trouve en le cherchant, pas en parcourant le site.
    $fullyBooked = Event::query()
        ->with('ticketTypes')
        ->get()
        ->filter(fn (Event $event) => $event->ticketTypes->isNotEmpty()
            && $event->ticketTypes->every(fn (TicketType $tier) => $tier->remainingTickets() === 0));

    expect($fullyBooked)->toHaveCount(3)
        ->and($fullyBooked->pluck('category_id')->unique())->toHaveCount(3);

    // Et aucune commande en attente ne retient de places sur eux : elle serait
    // annulée par `orders:cancel-unpaid` dans les minutes suivant le seeding, et
    // l'annulation rendrait ces places au stock — l'événement cesserait d'être
    // complet tout seul. Le planificateur ne tourne pas pendant les tests, donc
    // c'est cette condition-là qu'il faut vérifier, et non le compte final.
    $heldByPending = OrderItem::query()
        ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::PENDING->value))
        ->whereHas('ticketType', fn ($q) => $q->whereIn('event_id', $fullyBooked->pluck('id')))
        ->count();

    expect($heldByPending)->toBe(0);

    $partiallyBooked = Event::query()
        ->with('ticketTypes')
        ->get()
        ->filter(fn (Event $event) => $event->ticketTypes->count() > 1
            && $event->ticketTypes->contains(fn (TicketType $tier) => $tier->remainingTickets() === 0)
            && $event->ticketTypes->contains(fn (TicketType $tier) => $tier->remainingTickets() > 0));

    expect($partiallyBooked)->not->toBeEmpty();
});

it('creates paid and pending orders with issued tickets', function (): void {
    expect(Order::where('status', 'paid')->count())->toBeGreaterThan(0)
        ->and(Order::where('status', 'pending')->count())->toBeGreaterThan(0)
        ->and(Ticket::count())->toBeGreaterThan(0);
});

it('leaves the seed images on disk so it can run again', function (): void {
    // Media Library moves the source file unless `preservingOriginal()` is used;
    // without it the second run would silently produce imageless events.
    expect(glob(database_path('seeders/assets/events/*.webp')))->not->toBeEmpty()
        ->and(glob(database_path('seeders/assets/organizers/*.webp')))->not->toBeEmpty();
});
