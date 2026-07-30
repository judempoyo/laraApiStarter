<?php

declare(strict_types=1);

use App\Enums\ImportStatus;
use App\Enums\WebhookEvent;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('local');
    Queue::fake();
});

// ─── Resources listing ────────────────────────────────────────────────────────

it('user can list available import resources', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->getJson('/api/v1/user/imports/resources')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['resources']]);
});

// ─── Trigger import ───────────────────────────────────────────────────────────

it('user can trigger a user-scoped import', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $csvContent = "key,value\ntheme,dark\nlanguage,fr";
    $file = UploadedFile::fake()->createWithContent('preferences.csv', $csvContent);

    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
            'dry_run'  => false,
        ]);

    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.resource', 'user_preferences')
        ->assertJsonPath('data.status', 'pending');

    Queue::assertPushed(ProcessImportJob::class);

    $this->assertDatabaseHas('imports', [
        'user_id'  => $user->id,
        'resource' => 'user_preferences',
        'dry_run'  => false,
    ]);
});

it('user cannot trigger admin-only import', function (): void {
    $user = User::factory()->create();

    $csvContent = "name,email,password\nBob,bob@example.com,secret123";
    $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

    $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'users',
            'file'     => $file,
        ])
        ->assertForbidden();
});

it('admin can trigger admin-only import', function (): void {
    Queue::fake();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $csvContent = "name,email,password\nBob,bob@example.com,secret123";
    $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

    $this->actingAs($admin, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'users',
            'file'     => $file,
        ])
        ->assertStatus(202);

    Queue::assertPushed(ProcessImportJob::class);
});

// ─── Import execution ──────────────────────────────────────────────────────────

it('executes user preferences import successfully with valid CSV', function (): void {
    $user = User::factory()->create();

    $csvContent = "key,value\ntheme,dark\nlanguage,fr";
    $file = UploadedFile::fake()->createWithContent('preferences.csv', $csvContent);

    // Trigger import live
    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
            'dry_run'  => false,
        ])
        ->assertStatus(202);

    $import = Import::findOrFail($response->json('data.id'));

    // Process job synchronously
    $job = new ProcessImportJob($import);
    $job->handle();

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED);
    expect($import->total_rows)->toBe(2);
    expect($import->successful_rows)->toBe(2);
    expect($import->failed_rows)->toBe(0);

    // Verify database has the preference
    $this->assertDatabaseHas('user_preferences', [
        'user_id' => $user->id,
        'key'     => 'theme',
        'value'   => json_encode('dark'),
    ]);
});

it('performs validation only on dry run without saving to database', function (): void {
    $user = User::factory()->create();

    $csvContent = "key,value\ntheme,dark\nlanguage,fr";
    $file = UploadedFile::fake()->createWithContent('preferences.csv', $csvContent);

    // Trigger import with dry_run = true
    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
            'dry_run'  => true,
        ])
        ->assertStatus(202);

    $import = Import::findOrFail($response->json('data.id'));

    $job = new ProcessImportJob($import);
    $job->handle();

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED);
    expect($import->total_rows)->toBe(2);
    expect($import->successful_rows)->toBe(2);
    expect($import->failed_rows)->toBe(0);

    // Verify database does NOT have the preference
    $this->assertDatabaseMissing('user_preferences', [
        'user_id' => $user->id,
        'key'     => 'theme',
    ]);
});

it('fails validation when CSV headers are missing', function (): void {
    $user = User::factory()->create();

    // CSV missing the "value" header
    $csvContent = "key,wrong_header\ntheme,dark";
    $file = UploadedFile::fake()->createWithContent('preferences.csv', $csvContent);

    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
        ])
        ->assertStatus(202);

    $import = Import::findOrFail($response->json('data.id'));

    $job = new ProcessImportJob($import);
    try {
        $job->handle();
    } catch (\Throwable $e) {
        // Handled in try/catch in job
    }

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::FAILED);
    expect($import->error_message)->toContain('Missing required CSV headers');
});

it('logs validation errors for invalid rows', function (): void {
    $user = User::factory()->create();

    // CSV containing empty key (invalid row)
    $csvContent = "key,value\n,dark\nlanguage,fr";
    $file = UploadedFile::fake()->createWithContent('preferences.csv', $csvContent);

    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
        ])
        ->assertStatus(202);

    $import = Import::findOrFail($response->json('data.id'));

    $job = new ProcessImportJob($import);
    $job->handle();

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED);
    expect($import->total_rows)->toBe(2);
    expect($import->successful_rows)->toBe(1);
    expect($import->failed_rows)->toBe(1);
    expect($import->errors)->not->toBeEmpty();
    expect($import->errors[0]['row'])->toBe(1);
    expect($import->errors[0]['errors'])->toHaveKey('key');
});

it('handles JSON files correctly', function (): void {
    $user = User::factory()->create();

    $jsonContent = json_encode([
        ['key' => 'theme', 'value' => 'dark'],
        ['key' => 'language', 'value' => 'en'],
    ]);
    $file = UploadedFile::fake()->createWithContent('preferences.json', $jsonContent);

    $response = $this->actingAs($user, config('api.auth_guard', 'sanctum'))
        ->postJson('/api/v1/user/imports', [
            'resource' => 'user_preferences',
            'file'     => $file,
        ])
        ->assertStatus(202);

    $import = Import::findOrFail($response->json('data.id'));

    $job = new ProcessImportJob($import);
    $job->handle();

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED);
    expect($import->total_rows)->toBe(2);
    expect($import->successful_rows)->toBe(2);

    $this->assertDatabaseHas('user_preferences', [
        'user_id' => $user->id,
        'key'     => 'theme',
        'value'   => json_encode('dark'),
    ]);
});

// ─── Unauthenticated ──────────────────────────────────────────────────────────

it('unauthenticated user cannot access imports', function (): void {
    $this->getJson('/api/v1/user/imports')->assertUnauthorized();
});
