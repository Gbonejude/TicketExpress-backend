<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\CustomNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The badge used to be derived from the first page of notifications, so it was
 * only ever right for the first twenty, and "tout marquer comme lu" cost one
 * request per row. Both are now single statements.
 */
beforeEach(function (): void {
    $this->user = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $this->user->notify(new CustomNotification("Titre {$i}", "Message {$i}", 'info'));
    }
});

it('requires authentication', function (): void {
    $this->getJson('/api/v1/notifications/unread-count')->assertUnauthorized();
    $this->postJson('/api/v1/notifications/read-all')->assertUnauthorized();
});

it('counts unread notifications', function (): void {
    $this->actingAs($this->user)
        ->getJson('/api/v1/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread', 3);
});

it('does not count notifications already read', function (): void {
    $this->user->unreadNotifications()->first()->markAsRead();

    $this->actingAs($this->user)
        ->getJson('/api/v1/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread', 2);
});

it('marks every notification as read in one request', function (): void {
    $this->actingAs($this->user)
        ->postJson('/api/v1/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('data.marked', 3);

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

it('never counts another user notifications', function (): void {
    $other = User::factory()->create();
    $other->notify(new CustomNotification('Autre', 'Pas pour vous', 'info'));

    $this->actingAs($this->user)
        ->getJson('/api/v1/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.unread', 3);
});

it('leaves other users notifications alone when marking all read', function (): void {
    $other = User::factory()->create();
    $other->notify(new CustomNotification('Autre', 'Pas pour vous', 'info'));

    $this->actingAs($this->user)->postJson('/api/v1/notifications/read-all')->assertOk();

    expect($other->unreadNotifications()->count())->toBe(1);
});
