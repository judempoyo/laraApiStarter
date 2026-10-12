<?php

declare(strict_types=1);

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Jobs\ProcessExportJob;
use App\Models\Export;
use App\Models\User;
use App\Models\UserPreference;
use App\Providers\Modules\NotificationsServiceProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
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
            'format' => 'csv',
        ]);

    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.resource', 'user_preferences')
        ->assertJsonPath('data.status', 'pending');

    Queue::assertPushed(ProcessExportJob::class);

    $this->assertDatabaseHas('exports', [
        'user_id' => $user->id,
        'resource' => 'user_preferences',
        'format' => 'csv',
    ]);
})->skip(fn () => ! class_exists(UserPreference::class), 'Optional integration module is not installed');

it('user cannot trigger admin-only export', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format' => 'csv',
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
            'format' => 'json',
        ])
        ->assertStatus(202);

    Queue::assertPushed(ProcessExportJob::class);
})->skip(fn () => ! method_exists(User::class, 'hasRole'), 'Optional integration module is not installed');

it('export validates resource', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'invalid_resource',
            'format' => 'csv',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['resource']);
});

it('export validates format', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'user_preferences',
            'format' => 'txt',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['format']);
})->skip(fn () => ! class_exists(UserPreference::class), 'Optional integration module is not installed');

// ─── Filters ──────────────────────────────────────────────────────────────────

it('export accepts date range filters', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'notifications',
            'format' => 'json',
            'filters' => [
                'date_from' => '2025-01-01',
                'date_to' => '2025-12-31',
            ],
        ])
        ->assertStatus(202);
})->skip(fn () => ! class_exists(NotificationsServiceProvider::class), 'Optional integration module is not installed');

it('export accepts id range filters', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format' => 'csv',
            'filters' => [
                'id_from' => 1,
                'id_to' => 100,
            ],
        ])
        ->assertStatus(202);
})->skip(fn () => ! method_exists(User::class, 'hasRole'), 'Optional integration module is not installed');

it('export accepts specific ids filter', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/exports', [
            'resource' => 'users',
            'format' => 'csv',
            'filters' => [
                'ids' => [1, 5, 10],
            ],
        ])
        ->assertStatus(202);
})->skip(fn () => ! method_exists(User::class, 'hasRole'), 'Optional integration module is not installed');

// ─── List & Show ──────────────────────────────────────────────────────────────

it('user can list their exports', function (): void {
    $user = User::factory()->create();

    Export::factory()->count(3)->create([
        'user_id' => $user->id,
        'resource' => 'notifications',
        'format' => ExportFormat::CSV,
        'status' => ExportStatus::COMPLETED,
    ]);

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson('/api/v1/user/exports')
        ->assertOk()
        ->assertJsonPath('success', true);
})->skip(fn () => ! class_exists(NotificationsServiceProvider::class), 'Optional integration module is not installed');

it('user can view a pending export', function (): void {
    $user = User::factory()->create();

    $export = Export::factory()->create([
        'user_id' => $user->id,
        'resource' => 'notifications',
        'format' => ExportFormat::CSV,
        'status' => ExportStatus::PENDING,
    ]);

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson("/api/v1/user/exports/{$export->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.download_url', null);
})->skip(fn () => ! class_exists(NotificationsServiceProvider::class), 'Optional integration module is not installed');

it('user cannot view another users export', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $export = Export::factory()->create([
        'user_id' => $user2->id,
        'resource' => 'notifications',
        'format' => ExportFormat::CSV,
        'status' => ExportStatus::PENDING,
    ]);

    $this->actingAs($user1, config('api.auth_guard', 'sanctum'))
        ->getJson("/api/v1/user/exports/{$export->id}")
        ->assertNotFound();
})->skip(fn () => ! class_exists(NotificationsServiceProvider::class), 'Optional integration module is not installed');

// ─── Unauthenticated ──────────────────────────────────────────────────────────

it('unauthenticated user cannot access exports', function (): void {
    $this->getJson('/api/v1/user/exports')->assertUnauthorized();
});
