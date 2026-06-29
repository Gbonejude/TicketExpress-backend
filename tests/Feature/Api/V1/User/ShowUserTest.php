<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
});

it('shows a specific user', function () {
    $targetUser = User::factory()->create();

    $this->actingAs($this->user)
        ->getJson("/api/v1/users/{$targetUser->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $targetUser->id);
});
