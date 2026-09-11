<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function bannerManager(): array
{
    $user = User::factory()->create();
    Role::findOrCreate('organizer-manager');
    $user->assignRole('organizer-manager');
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $organizer = Organizer::factory()->create(['user_id' => $user->id]);

    return [$user, $organizer];
}

it('updates an event with a banner via method-spoofed multipart PUT', function (): void {
    [$manager, $organizer] = bannerManager();
    $event = Event::factory()->create(['organizer_id' => $organizer->id, 'title' => 'Avant']);

    $response = $this->actingAs($manager)->post("/api/v1/events/{$event->id}", [
        '_method' => 'PUT',
        'title' => 'Après',
        'banner' => UploadedFile::fake()->image('banner.jpg', 800, 400),
    ]);

    $response->assertOk();
    expect($event->fresh()->title)->toBe('Après');
});

it('rejects a banner heavier than 2 MB with a 422', function (): void {
    [$manager, $organizer] = bannerManager();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    // Une photo de téléphone dépasse couramment 2 Mo.
    $this->actingAs($manager)->postJson("/api/v1/events/{$event->id}", [
        '_method' => 'PUT',
        'title' => 'Après',
        'banner' => UploadedFile::fake()->create('photo.jpg', 3500, 'image/jpeg'),
    ])->assertStatus(422)->assertJsonValidationErrors('banner');
});

it('updates with the full payload the dashboard sends, banner included', function (): void {
    [$manager, $organizer] = bannerManager();
    $category = \App\Models\EventCategory::factory()->create();
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'slug' => 'ma-soiree',
    ]);

    $this->actingAs($manager)->post("/api/v1/events/{$event->id}", [
        '_method' => 'PUT',
        'organizer_id' => $organizer->id,
        'category_id' => $category->id,
        'title' => 'Après',
        'slug' => 'ma-soiree',
        'description' => 'Description mise à jour',
        'start_date' => now()->addWeek()->toDateTimeString(),
        'end_date' => now()->addWeek()->addHours(3)->toDateTimeString(),
        'status' => 'draft',
        'event_type' => 'physical',
        // Le dashboard envoie désormais les booléens en "1"/"0" en multipart :
        // la règle `boolean` de Laravel refuse "true"/"false".
        'refund_allowed' => '1',
        'refund_days_before' => '2',
        'banner' => UploadedFile::fake()->image('banner.jpg', 800, 400),
    ])->assertOk();
});

it('updates an event with a banner as an admin', function (): void {
    $admin = User::factory()->create();
    Role::findOrCreate('admin');
    $admin->assignRole('admin');
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $event = Event::factory()->create(['title' => 'Avant']);

    $this->actingAs($admin)->post("/api/v1/events/{$event->id}", [
        '_method' => 'PUT',
        'title' => 'Après',
        'banner' => UploadedFile::fake()->image('banner.jpg', 800, 400),
    ])->assertOk();
});
