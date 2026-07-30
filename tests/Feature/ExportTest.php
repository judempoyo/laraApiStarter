<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── Resources listing ────────────────────────────────────────────────────────

it('user can list available export resources', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson('/api/v1/user/exports/resources')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['resources', 'formats']]);
});

// ─── Trigger export ───────────────────────────────────────────────────────────

it('user can trigger a user-scoped export', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'user_preferences',
            'format'   => 'csv',
        ]);

    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.resource', 'user_preferences')
        ->assertJsonPath('data.status', 'pending');

    Queue::assertPushed(ProcessExportJob::class);

    $this->assertDatabaseHas('exports', [
        'user_id'  => $user->id,
        'resource' => 'user_preferences',
        'format'   => 'csv',
    ]);
});

it('user cannot trigger admin-only export', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format'   => 'csv',
        ])
        ->assertForbidden();
});

it('admin can trigger admin-only export', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format'   => 'json',
        ])
        ->assertStatus(202);

    Queue::assertPushed(ProcessExportJob::class);
});

it('export validates resource', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'invalid_resource',
            'format'   => 'csv',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['resource']);
});

it('export validates format', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'user_preferences',
            'format'   => 'txt',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['format']);
});

// ─── Filters ──────────────────────────────────────────────────────────────────

it('export accepts date range filters', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'notifications',
            'format'   => 'json',
            'filters'  => [
                'date_from' => '2025-01-01',
                'date_to'   => '2025-12-31',
            ],
        ])
        ->assertStatus(202);
});

it('export accepts id range filters', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format'   => 'csv',
            'filters'  => [
                'id_from' => 1,
                'id_to'   => 100,
            ],
        ])
        ->assertStatus(202);
});

it('export accepts specific ids filter', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format'   => 'csv',
            'filters'  => [
                'ids' => [1, 5, 10],
            ],
        ])
        ->assertStatus(202);
});

// ─── List & Show ──────────────────────────────────────────────────────────────

it('user can list their exports', function (): void {
    $user = User::factory()->create();

    Export::factory()->count(3)->create([
        'user_id'  => $user->id,
        'resource' => 'notifications',
        'format'   => ExportFormat::CSV,
        'status'   => ExportStatus::COMPLETED,
    ]);

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson('/api/v1/user/exports')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('user can view a pending export', function (): void {
    $user = User::factory()->create();

    $export = Export::factory()->create([
        'user_id'  => $user->id,
        'resource' => 'notifications',
        'format'   => ExportFormat::CSV,
        'status'   => ExportStatus::PENDING,
    ]);

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson("/api/v1/user/exports/{$export->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.download_url', null);
});

it('user cannot view another users export', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $export = Export::factory()->create([
        'user_id'  => $user2->id,
        'resource' => 'notifications',
        'format'   => ExportFormat::CSV,
        'status'   => ExportStatus::PENDING,
    ]);

    $this->actingAs($user1, config('api.auth_guard', 'sanctum'))
        ->getJson("/api/v1/user/exports/{$export->id}")
        ->assertNotFound();
});

// ─── Unauthenticated ──────────────────────────────────────────────────────────

it('unauthenticated user cannot access exports', function (): void {
    $this->getJson('/api/v1/user/exports')->assertUnauthorized();
});
