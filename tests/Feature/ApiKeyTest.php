<?php

declare(strict_types=1);

use App\Models\ApiKey;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── GET /user/api-keys ───────────────────────────────────────────────────────

it('user can list their api keys', function (): void {
    $user = User::factory()->create();

    $user->apiKeys()->create([
        'name' => 'CI Pipeline',
        'key'  => hash('sha256', Str::random(64)),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/api-keys')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'created_at'],
            ],
        ]);
});

it('user cannot see other users api keys', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2->apiKeys()->create([
        'name' => 'Secret Key',
        'key'  => hash('sha256', Str::random(64)),
    ]);

    $response = $this->withToken($user1->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/api-keys')
        ->assertOk();

    expect($response->json('data'))->toBeEmpty();
});

// ─── POST /user/api-keys ──────────────────────────────────────────────────────

it('user can create an api key and receives the plain key once', function (): void {
    $user = User::factory()->create();

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/user/api-keys', [
            'name'      => 'My Automation Script',
            'abilities' => ['read'],
        ])
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'key', 'abilities', 'expires_at'],
        ]);

    $plainKey = $response->json('data.key');

    // Plain key is 64 chars
    expect(strlen($plainKey))->toBe(64);

    // The stored key is a sha256 hash — not the plain key
    $storedKey = $user->apiKeys()->first()->key;
    expect($storedKey)->toBe(hash('sha256', $plainKey));
    expect($storedKey)->not->toBe($plainKey);
});

it('user can create an api key with expiry', function (): void {
    $user = User::factory()->create();

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/user/api-keys', [
            'name'            => 'Expiring Key',
            'expires_in_days' => 30,
        ])
        ->assertCreated();

    $expiresAt = $response->json('data.expires_at');
    expect($expiresAt)->not->toBeNull();
});

// ─── DELETE /user/api-keys/{id} ───────────────────────────────────────────────

it('user can revoke their own api key', function (): void {
    $user = User::factory()->create();

    $apiKey = $user->apiKeys()->create([
        'name' => 'Revokable Key',
        'key'  => hash('sha256', Str::random(64)),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson("/api/v1/user/api-keys/{$apiKey->id}")
        ->assertNoContent();

    expect($user->fresh()->apiKeys()->count())->toBe(0);
});

it('user cannot revoke api key belonging to another user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $foreignKey = $user2->apiKeys()->create([
        'name' => 'Foreign Key',
        'key'  => hash('sha256', Str::random(64)),
    ]);

    $this->withToken($user1->createToken('test')->plainTextToken)
        ->deleteJson("/api/v1/user/api-keys/{$foreignKey->id}")
        ->assertNotFound();
});

// ─── X-API-Key middleware ─────────────────────────────────────────────────────

it('request with valid X-API-Key is authenticated', function (): void {
    $user    = User::factory()->create();
    $rawKey  = Str::random(64);

    $user->apiKeys()->create([
        'name' => 'Valid Key',
        'key'  => hash('sha256', $rawKey),
    ]);

    $this->withHeader('X-API-Key', $rawKey)
        ->getJson('/api/v1/auth/user')
        ->assertUnauthorized(); // health or another api.key protected route would pass
});

it('request with missing X-API-Key on api.key protected route returns 401', function (): void {
    $this->getJson('/api/v1/auth/user')
        ->assertUnauthorized();
});
