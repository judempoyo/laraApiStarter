<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── GET /user/preferences ────────────────────────────────────────────────────

it('user can list their preferences as a key-value map', function (): void {
    $user = User::factory()->create();

    $user->preferences()->createMany([
        ['key' => 'theme', 'value' => 'dark'],
        ['key' => 'lang', 'value' => 'fr'],
    ]);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/preferences')
        ->assertOk();

    expect($response->json('data.theme'))->toBe('dark');
    expect($response->json('data.lang'))->toBe('fr');
});

it('empty preferences returns an empty object', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/preferences')
        ->assertOk()
        ->assertJsonFragment(['data' => []]);
});

// ─── PUT /user/preferences/{key} ──────────────────────────────────────────────

it('user can create a new preference', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->putJson('/api/v1/user/preferences/theme', ['value' => 'dark'])
        ->assertOk();

    $this->assertDatabaseHas('user_preferences', [
        'user_id' => $user->id,
        'key'     => 'theme',
    ]);
});

it('user can update an existing preference', function (): void {
    $user = User::factory()->create();
    $user->preferences()->create(['key' => 'theme', 'value' => 'light']);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->putJson('/api/v1/user/preferences/theme', ['value' => 'dark'])
        ->assertOk();

    expect(
        $user->fresh()->preferences()->where('key', 'theme')->first()->value
    )->toBe('dark');
});

it('preference value can be a json object', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->putJson('/api/v1/user/preferences/notifications', ['value' => ['email' => true, 'sms' => false]])
        ->assertOk();

    $pref = $user->fresh()->preferences()->where('key', 'notifications')->first();
    expect($pref->value['email'])->toBeTrue();
    expect($pref->value['sms'])->toBeFalse();
});

// ─── DELETE /user/preferences/{key} ──────────────────────────────────────────

it('user can delete an existing preference', function (): void {
    $user = User::factory()->create();
    $user->preferences()->create(['key' => 'theme', 'value' => 'dark']);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson('/api/v1/user/preferences/theme')
        ->assertNoContent();

    $this->assertDatabaseMissing('user_preferences', [
        'user_id' => $user->id,
        'key'     => 'theme',
    ]);
});

it('deleting a non-existent preference returns 404', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson('/api/v1/user/preferences/non_existent')
        ->assertNotFound();
});

it('preferences are isolated per user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user2->preferences()->create(['key' => 'theme', 'value' => 'dark']);

    $response = $this->withToken($user1->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/preferences')
        ->assertOk();

    expect($response->json('data'))->not->toHaveKey('theme');
});
