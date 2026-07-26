<?php

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\ResolveDeviceNameAction;
use App\Actions\Auth\SocialiteLoginAction;
use App\DTOs\Auth\LoginDTO;
use App\Enums\Result\Auth\LoginResult;
use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── LoginUserAction ──────────────────────────────────────────────────────────

it('logs in user with valid credentials', function () {
    User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $result = app(LoginUserAction::class)->execute(
        new LoginDTO(email: 'test@example.com', password: 'password')
    );

    expect($result['status'])->toBe(LoginResult::SUCCESS);
    expect($result['token'])->not->toBeNull();
    expect($result['token_type'])->toBe('Bearer');
});

it('returns INVALID_CREDENTIALS when password is wrong', function () {
    User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $result = app(LoginUserAction::class)->execute(
        new LoginDTO(email: 'test@example.com', password: 'wrong-password')
    );

    expect($result['status'])->toBe(LoginResult::INVALID_CREDENTIALS);
});

it('returns INVALID_CREDENTIALS when user does not exist', function () {
    $result = app(LoginUserAction::class)->execute(
        new LoginDTO(email: 'nobody@example.com', password: 'password')
    );

    expect($result['status'])->toBe(LoginResult::INVALID_CREDENTIALS);
});

it('returns USER_DISABLED when account is disabled', function () {
    User::factory()->create([
        'email'    => 'disabled@example.com',
        'password' => bcrypt('password'),
        'status'   => \App\Enums\UserStatus::DISABLED,
    ]);

    $result = app(LoginUserAction::class)->execute(
        new LoginDTO(email: 'disabled@example.com', password: 'password')
    );

    expect($result['status'])->toBe(LoginResult::USER_DISABLED);
});

// ─── ResolveDeviceNameAction ──────────────────────────────────────────────────

it('resolves device name from explicit Device-Name header', function () {
    $request = \Illuminate\Http\Request::create('/test');
    $request->headers->set('Device-Name', 'iPhone de Jude');

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('iPhone de Jude');
});

it('resolves Android from User-Agent when no header', function () {
    $request = \Illuminate\Http\Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('Android');
});

it('resolves iOS from User-Agent', function () {
    $request = \Illuminate\Http\Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0)',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('iOS');
});

it('resolves Chrome from User-Agent', function () {
    $request = \Illuminate\Http\Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Chrome/110.0.0.0 Safari/537.36',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('Chrome');
});

it('falls back to API when User-Agent is unknown', function () {
    $request = \Illuminate\Http\Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'CustomBot/1.0',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('API');
});

// ─── SocialiteLoginAction ─────────────────────────────────────────────────────

it('creates a new user via Google OAuth', function () {
    $socialUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
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
        'email'    => 'existing@example.com',
        'password' => bcrypt('password'),
    ]);

    $socialUser = Mockery::mock(\Laravel\Socialite\Contracts\User::class);
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
