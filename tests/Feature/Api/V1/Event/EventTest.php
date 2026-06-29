<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ScreenPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('screen.events');
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
    $this->organizer = Organizer::factory()->for($this->user)->create();
});

it('can list events with filters', function (): void {
    Event::factory()->count(5)->create(['status' => EventStatus::PUBLISHED]);
    Event::factory()->count(2)->create(['status' => EventStatus::DRAFT]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/events?status=published');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'status'],
            ],
        ]);

    expect(count($response->json('data')))->toBe(5);
});

it('can create event with ticket types', function (): void {
    $category = EventCategory::factory()->create();
    $venue = Venue::factory()->create();

    $payload = [
        'organizer_id' => $this->organizer->id,
        'category_id' => $category->id,
        'venue_id' => $venue->id,
        'title' => 'Test Event',
        'slug' => 'test-event-2024',
        'description' => 'This is a test event description.',
        'start_date' => now()->addWeek()->toDateTimeString(),
        'end_date' => now()->addWeek()->addHours(3)->toDateTimeString(),
        'max_attendees' => 500,
        'status' => 'draft',
        'ticket_types' => [
            [
                'name' => 'VIP',
                'price' => 50000,
                'quantity' => 50,
                'description' => 'VIP access',
            ],
            [
                'name' => 'Standard',
                'price' => 20000,
                'quantity' => 200,
                'description' => 'General admission',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/events', $payload);

    $response->assertCreated()
        ->assertJson(['success' => true])
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'status',
                'ticketTypes',
            ],
        ]);

    expect(Event::where('slug', 'test-event-2024')->exists())->toBeTrue();
    expect(TicketType::where('name', 'VIP')->exists())->toBeTrue();
    expect(TicketType::where('name', 'Standard')->exists())->toBeTrue();
});

it('can publish event with ticket types', function (): void {
    $event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
        'status' => EventStatus::DRAFT,
    ]);

    TicketType::factory()->for($event)->create(['name' => 'Standard']);

    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/events/{$event->id}/publish");

    $response->assertOk()
        ->assertJson(['success' => true]);

    $event->refresh();
    expect($event->status)->toBe(EventStatus::PUBLISHED);
});

it('cannot publish event without ticket types', function (): void {
    $event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
        'status' => EventStatus::DRAFT,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/events/{$event->id}/publish");

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('types de tickets');
});

it('can update event', function (): void {
    $event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
        'title' => 'Old Title',
    ]);

    $payload = [
        'title' => 'New Title',
        'description' => 'Updated description',
    ];

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/events/{$event->id}", $payload);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $event->refresh();
    expect($event->title)->toBe('New Title');
});

it('can delete event', function (): void {
    $event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/events/{$event->id}");

    $response->assertNoContent();

    expect(Event::find($event->id))->toBeNull();
});

it('requires authentication for event management', function (): void {
    $event = Event::factory()->create();

    // GET is public, but POST/PUT/DELETE require auth
    $this->postJson('/api/v1/events', [])->assertUnauthorized();
    $this->putJson("/api/v1/events/{$event->id}", [])->assertUnauthorized();
    $this->deleteJson("/api/v1/events/{$event->id}")->assertUnauthorized();
    $this->postJson("/api/v1/events/{$event->id}/publish")->assertUnauthorized();
});
