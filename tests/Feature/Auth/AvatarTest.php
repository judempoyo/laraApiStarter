<?php

use App\Actions\Auth\UploadAvatarAction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
});

// ─── UploadAvatarAction unit ──────────────────────────────────────────────────

it('stores avatar and updates user model', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $updated = app(UploadAvatarAction::class)->execute($user, $file);

    expect($updated->avatar)->not->toBeNull();
    expect(Storage::disk('public')->exists($updated->avatar))->toBeTrue();
});

it('deletes old local avatar when uploading a new one', function () {
    $user = User::factory()->create(['avatar' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'fake');

    $file = UploadedFile::fake()->image('new.jpg');

    app(UploadAvatarAction::class)->execute($user, $file);

    expect(Storage::disk('public')->exists('avatars/old.jpg'))->toBeFalse();
});

it('does not attempt to delete external OAuth avatar URL', function () {
    $user = User::factory()->create([
        'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
    ]);

    $file = UploadedFile::fake()->image('local.jpg');

    // Should not throw — no local file to delete
    $updated = app(UploadAvatarAction::class)->execute($user, $file);

    expect($updated->avatar)->not->toContain('googleusercontent');
});

// ─── avatar_url accessor ──────────────────────────────────────────────────────

it('avatar_url returns null when no avatar is set', function () {
    $user = User::factory()->create(['avatar' => null]);

    expect($user->avatar_url)->toBeNull();
});

it('avatar_url returns external URL as-is', function () {
    $user = User::factory()->create([
        'avatar' => 'https://lh3.googleusercontent.com/photo.jpg',
    ]);

    expect($user->avatar_url)->toBe('https://lh3.googleusercontent.com/photo.jpg');
});

it('avatar_url wraps local path with Storage::url', function () {
    $user = User::factory()->create(['avatar' => 'avatars/test.jpg']);

    expect($user->avatar_url)->toContain('avatars/test.jpg');
    expect($user->avatar_url)->not->toStartWith('https://lh3');
});

// ─── HTTP endpoints ───────────────────────────────────────────────────────────

it('POST /profile/avatar uploads and returns avatar_url', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $file  = UploadedFile::fake()->image('photo.jpg', 100, 100);

    $this->withToken($token)
        ->postJson('/api/v1/auth/profile/avatar', ['avatar' => $file])
        ->assertOk()
        ->assertJsonStructure(['data' => ['avatar_url']]);
});

it('POST /profile/avatar rejects non-image files', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $file  = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

    $this->withToken($token)
        ->postJson('/api/v1/auth/profile/avatar', ['avatar' => $file])
        ->assertUnprocessable();
});

it('DELETE /profile/avatar removes avatar', function () {
    Storage::disk('public')->put('avatars/remove-me.jpg', 'fake');

    $user  = User::factory()->create(['avatar' => 'avatars/remove-me.jpg']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/auth/profile/avatar')
        ->assertOk();

    expect($user->fresh()->avatar)->toBeNull();
    expect(Storage::disk('public')->exists('avatars/remove-me.jpg'))->toBeFalse();
});

it('DELETE /profile/avatar returns 404 when no avatar is set', function () {
    $user  = User::factory()->create(['avatar' => null]);
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/auth/profile/avatar')
        ->assertStatus(404);
});
