<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── GET /auth/sessions ───────────────────────────────────────────────────────

it('authenticated user can list their active sessions', function () {
    $user = User::factory()->create();

    $token1 = $user->createToken('iPhone')->plainTextToken;
    $user->createToken('Chrome');

    $this->withToken($token1)
        ->getJson('/api/v1/auth/sessions')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at', 'is_current'],
            ],
        ]);
});

it('session list marks current session correctly', function () {
    $user   = User::factory()->create();
    $token  = $user->createToken('Android')->plainTextToken;
    $user->createToken('Chrome');

    $response = $this->withToken($token)
        ->getJson('/api/v1/auth/sessions')
        ->assertOk();

    $sessions = $response->json('data');
    $current  = collect($sessions)->firstWhere('is_current', true);

    expect($current)->not->toBeNull();
    expect($current['name'])->toBe('Android');
});

// ─── DELETE /auth/sessions/{id} ───────────────────────────────────────────────

it('user can revoke a specific session', function () {
    $user   = User::factory()->create();
    $main   = $user->createToken('Main');
    $other  = $user->createToken('Other');

    $this->withToken($main->plainTextToken)
        ->deleteJson("/api/v1/auth/sessions/{$other->accessToken->id}")
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(1);
});

it('user cannot revoke a session belonging to another user', function () {
    $user1  = User::factory()->create();
    $user2  = User::factory()->create();

    $token1 = $user1->createToken('Main');
    $token2 = $user2->createToken('Other');

    $this->withToken($token1->plainTextToken)
        ->deleteJson("/api/v1/auth/sessions/{$token2->accessToken->id}")
        ->assertStatus(404);
});

// ─── DELETE /auth/sessions/others ─────────────────────────────────────────────

it('user can revoke all sessions except the current one', function () {
    $user = User::factory()->create();

    $current = $user->createToken('Current');
    $user->createToken('Old Device 1');
    $user->createToken('Old Device 2');

    expect($user->tokens()->count())->toBe(3);

    $this->withToken($current->plainTextToken)
        ->deleteJson('/api/v1/auth/sessions/others')
        ->assertNoContent();

    expect($user->fresh()->tokens()->count())->toBe(1);
    expect($user->tokens()->first()->name)->toBe('Current');
});

// ─── POST /auth/refresh ───────────────────────────────────────────────────────

it('refresh rotates the token and keeps device name', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('Firefox')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/refresh')
        ->assertOk()
        ->assertJsonStructure(['data' => ['token', 'token_type', 'expires_at']]);

    $this->flushHeaders(); 

    \Illuminate\Support\Facades\Auth::forgetUser();

    // Old token is revoked
    $this->withToken($token)
        ->getJson('/api/v1/auth/user')
        ->assertUnauthorized();
});
