<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

/**
 * Helper: insert a raw database notification for a user.
 */
function createNotificationFor(User $user, bool $read = false): DatabaseNotification
{
    return DatabaseNotification::create([
        'id'              => Str::uuid(),
        'type'            => 'App\Notifications\TestNotification',
        'notifiable_id'   => $user->id,
        'notifiable_type' => User::class,
        'data'            => ['message' => 'Hello'],
        'read_at'         => $read ? now() : null,
    ]);
}

// ─── GET /user/notifications ──────────────────────────────────────────────────

it('user can list their notifications', function (): void {
    $user = User::factory()->create();

    createNotificationFor($user);
    createNotificationFor($user, read: true);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/notifications')
        ->assertOk();

    expect($response->json('meta.total_items'))->toBe(2);
});

it('notifications are isolated per user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    createNotificationFor($user2);

    $response = $this->withToken($user1->createToken('test')->plainTextToken)
        ->getJson('/api/v1/user/notifications')
        ->assertOk();

    expect($response->json('meta.total_items'))->toBe(0);
});

// ─── POST /user/notifications/{id}/read ──────────────────────────────────────

it('user can mark a notification as read', function (): void {
    $user         = User::factory()->create();
    $notification = createNotificationFor($user);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson("/api/v1/user/notifications/{$notification->id}/read")
        ->assertNoContent();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marking a non-existent notification returns 404', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/user/notifications/' . Str::uuid() . '/read')
        ->assertNotFound();
});

// ─── POST /user/notifications/read-all ───────────────────────────────────────

it('user can mark all notifications as read', function (): void {
    $user = User::factory()->create();

    createNotificationFor($user);
    createNotificationFor($user);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/user/notifications/read-all')
        ->assertNoContent();

    $unread = $user->fresh()->unreadNotifications()->count();
    expect($unread)->toBe(0);
});

// ─── DELETE /user/notifications/{id} ─────────────────────────────────────────

it('user can delete a notification', function (): void {
    $user         = User::factory()->create();
    $notification = createNotificationFor($user);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson("/api/v1/user/notifications/{$notification->id}")
        ->assertNoContent();

    expect(DatabaseNotification::find($notification->id))->toBeNull();
});

it('user cannot delete a notification belonging to another user', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $otherNotification = createNotificationFor($user2);

    $this->withToken($user1->createToken('test')->plainTextToken)
        ->deleteJson("/api/v1/user/notifications/{$otherNotification->id}")
        ->assertNotFound();
});
