<?php

use App\Models\User;

it('revokes the current access token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful()
        ->assertJson(['success' => true, 'message' => 'Logged out successfully.']);

    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('does not revoke tokens for other devices on logout', function () {
    $user = User::factory()->create();
    $user->createToken('device-1');
    $token2 = $user->createToken('device-2')->plainTextToken;

    $this->withToken($token2)
        ->postJson('/api/v1/auth/logout')
        ->assertSuccessful();

    expect($user->fresh()->tokens()->count())->toBe(1);
});

it('returns 401 when the request is unauthenticated', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});
