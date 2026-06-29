<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
});

it('deletes a user', function () {
    $targetUser = User::factory()->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/v1/users/{$targetUser->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});
