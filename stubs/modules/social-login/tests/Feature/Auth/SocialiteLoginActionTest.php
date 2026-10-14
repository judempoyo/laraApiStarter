<?php

use App\Actions\Auth\SocialiteLoginAction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

// ─── SocialiteLoginAction ─────────────────────────────────────────────────────

it('creates a new user via Google OAuth', function () {
    $socialUser = Mockery::mock(Laravel\Socialite\Contracts\User::class);
    $socialUser->shouldReceive('getEmail')->andReturn('google@example.com');
    $socialUser->shouldReceive('getName')->andReturn('Google User');
    $socialUser->shouldReceive('getNickname')->andReturn(null);
    $socialUser->shouldReceive('getId')->andReturn('google-id-123');
    $socialUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

    $result = app(SocialiteLoginAction::class)->execute($socialUser, 'google');

    expect($result['token'])->not->toBeNull();
    expect($result['user']->email)->toBe('google@example.com');
    expect($result['user']->google_id)->toBe('google-id-123');
    expect($result['user']->provider)->toBe('google');
    expect($result['user']->email_verified_at)->not->toBeNull();

    expect(User::where('email', 'google@example.com')->exists())->toBeTrue();
});

it('links existing user when Google email matches', function () {
    $existing = User::factory()->create([
        'email' => 'existing@example.com',
        'password' => bcrypt('password'),
    ]);

    $socialUser = Mockery::mock(Laravel\Socialite\Contracts\User::class);
    $socialUser->shouldReceive('getEmail')->andReturn('existing@example.com');
    $socialUser->shouldReceive('getName')->andReturn('Existing User');
    $socialUser->shouldReceive('getNickname')->andReturn(null);
    $socialUser->shouldReceive('getId')->andReturn('google-id-456');
    $socialUser->shouldReceive('getAvatar')->andReturn(null);

    $result = app(SocialiteLoginAction::class)->execute($socialUser, 'google');

    // No new user created
    expect(User::where('email', 'existing@example.com')->count())->toBe(1);
    expect($result['user']->id)->toBe($existing->id);
    expect($result['user']->google_id)->toBe('google-id-456');
});
