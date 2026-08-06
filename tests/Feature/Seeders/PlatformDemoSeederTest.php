<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventOccurrence;
use App\Models\Order;
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

it('gives every organizer a logo and a Togolese contact', function (): void {
    Organizer::query()->with(['media', 'user'])->get()->each(function (Organizer $organizer): void {
        expect($organizer->logo)->not->toBeNull()
            ->and($organizer->user)->not->toBeNull()
            ->and($organizer->user->phone)->toStartWith('+228');
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
