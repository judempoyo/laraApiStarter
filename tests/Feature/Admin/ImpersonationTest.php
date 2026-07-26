<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── POST /admin/impersonate/{userId} ─────────────────────────────────────────

it('admin can impersonate a regular user', function (): void {
    $admin  = User::factory()->create();
    $target = User::factory()->create();

    $admin->assignRole('admin');
    $target->assignRole('user');

    $response = $this->withToken($admin->createToken('test')->plainTextToken)
        ->postJson("/api/v1/admin/impersonate/{$target->id}")
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['user', 'token', 'token_type', 'expires_at'],
        ]);

    expect($response->json('data.user.id'))->toBe($target->id);
});

it('admin cannot impersonate themselves', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->withToken($admin->createToken('test')->plainTextToken)
        ->postJson("/api/v1/admin/impersonate/{$admin->id}")
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'IMPERSONATION_SELF']);
});

it('admin cannot impersonate another admin', function (): void {
    $admin1 = User::factory()->create();
    $admin2 = User::factory()->create();

    $admin1->assignRole('admin');
    $admin2->assignRole('admin');

    $this->withToken($admin1->createToken('test')->plainTextToken)
        ->postJson("/api/v1/admin/impersonate/{$admin2->id}")
        ->assertStatus(403)
        ->assertJsonFragment(['code' => 'FORBIDDEN']);
});

it('impersonation returns 404 for non-existent user', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->withToken($admin->createToken('test')->plainTextToken)
        ->postJson('/api/v1/admin/impersonate/99999')
        ->assertNotFound();
});

it('non-admin user cannot access impersonation endpoint', function (): void {
    $user   = User::factory()->create();
    $target = User::factory()->create();

    $user->assignRole('user');

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson("/api/v1/admin/impersonate/{$target->id}")
        ->assertForbidden();
});

// ─── DELETE /admin/impersonate ────────────────────────────────────────────────

it('admin can stop impersonation session', function (): void {
    $admin  = User::factory()->create();
    $target = User::factory()->create();

    $admin->assignRole('admin');
    $target->assignRole('user');

    // Get impersonation token
    $response = $this->withToken($admin->createToken('admin-session')->plainTextToken)
        ->postJson("/api/v1/admin/impersonate/{$target->id}")
        ->assertOk();

    $impersonationToken = $response->json('data.token');

    // Use the impersonation token to stop the session
    $this->withToken($impersonationToken)
        ->deleteJson('/api/v1/admin/impersonate')
        ->assertNoContent();
});
