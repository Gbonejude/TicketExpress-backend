<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
});

it('updates a user', function () {
    $targetUser = User::factory()->create(['first_name' => 'OldName']);

    $this->actingAs($this->user)
        ->putJson("/api/v1/users/{$targetUser->id}", ['first_name' => 'NewName'])
        ->assertOk()
        ->assertJsonPath('data.firstName', 'NewName');

    expect($targetUser->fresh()->first_name)->toBe('NewName');
});
