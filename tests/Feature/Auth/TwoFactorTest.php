<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use PragmaRX\Google2FA\Google2FA;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── POST /auth/two-factor/enable ─────────────────────────────────────────────

it('authenticated user can initiate 2FA setup', function (): void {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/enable')
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['secret', 'qr_code_uri'],
        ]);

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('cannot enable 2FA when already confirmed', function (): void {
    $user = User::factory()->create([
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/enable')
        ->assertStatus(409)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_ALREADY_ENABLED']);
});

// ─── POST /auth/two-factor/confirm ────────────────────────────────────────────

it('user can confirm 2FA with a valid code', function (): void {
    $google2fa = new Google2FA();
    $secret    = $google2fa->generateSecretKey(32);

    $user = User::factory()->create([
        'two_factor_secret'       => encrypt($secret),
        'two_factor_confirmed_at' => null,
    ]);

    $code = $google2fa->getCurrentOtp($secret);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/confirm', ['code' => $code])
        ->assertOk();

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('confirm 2FA rejects an invalid code', function (): void {
    $secret = (new Google2FA())->generateSecretKey(32);

    $user = User::factory()->create([
        'two_factor_secret'       => encrypt($secret),
        'two_factor_confirmed_at' => null,
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/confirm', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_INVALID_CODE']);
});

// ─── POST /auth/two-factor/verify ─────────────────────────────────────────────

it('user can verify a valid TOTP code', function (): void {
    $google2fa = new Google2FA();
    $secret    = $google2fa->generateSecretKey(32);

    $user = User::factory()->create([
        'two_factor_secret'       => encrypt($secret),
        'two_factor_confirmed_at' => now(),
    ]);

    $code = $google2fa->getCurrentOtp($secret);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/verify', ['code' => $code])
        ->assertOk();
});

it('verify returns 422 when 2FA is not enabled', function (): void {
    $user = User::factory()->create([
        'two_factor_secret'       => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/verify', ['code' => '123456'])
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_NOT_ENABLED']);
});

// ─── DELETE /auth/two-factor ──────────────────────────────────────────────────

it('user can disable 2FA with correct password', function (): void {
    $user = User::factory()->create([
        'password'                => bcrypt('secret'),
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson('/api/v1/auth/two-factor', ['password' => 'secret'])
        ->assertNoContent();

    expect($user->fresh()->two_factor_secret)->toBeNull();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('disable 2FA fails with wrong password', function (): void {
    $user = User::factory()->create([
        'password'                => bcrypt('correct'),
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson('/api/v1/auth/two-factor', ['password' => 'wrong'])
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'PASSWORD_MISMATCH']);
});
