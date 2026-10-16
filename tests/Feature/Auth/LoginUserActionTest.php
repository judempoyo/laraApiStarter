<?php

declare(strict_types=1);

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\ResolveDeviceNameAction;
use App\DTOs\Auth\LoginDTO;
use App\Enums\Result\Auth\LoginResult;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

// ─── LoginUserAction ──────────────────────────────────────────────────────────

it('logs in user with valid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
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
        'email' => 'test@example.com',
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
        'email' => 'disabled@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::DISABLED,
    ]);

    $result = app(LoginUserAction::class)->execute(
        new LoginDTO(email: 'disabled@example.com', password: 'password')
    );

    expect($result['status'])->toBe(LoginResult::USER_DISABLED);
});

// ─── ResolveDeviceNameAction ──────────────────────────────────────────────────

it('resolves device name from explicit Device-Name header', function () {
    $request = Request::create('/test');
    $request->headers->set('Device-Name', 'iPhone de Jude');

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('iPhone de Jude');
});

it('resolves Android from User-Agent when no header', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 12) AppleWebKit/537.36',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('Android');
});

it('resolves iOS from User-Agent', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0)',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('iOS');
});

it('resolves Chrome from User-Agent', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 Chrome/110.0.0.0 Safari/537.36',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('Chrome');
});

it('falls back to API when User-Agent is unknown', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_USER_AGENT' => 'CustomBot/1.0',
    ]);

    $name = app(ResolveDeviceNameAction::class)->execute($request);

    expect($name)->toBe('API');
});
