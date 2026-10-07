<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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

it('user can confirm 2FA with a valid code and receives 8 recovery codes', function (): void {
    $google2fa = new Google2FA();
    $secret    = $google2fa->generateSecretKey(32);

    $user = User::factory()->create([
        'two_factor_secret'       => encrypt($secret),
        'two_factor_confirmed_at' => null,
    ]);

    $code = $google2fa->getCurrentOtp($secret);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/confirm', ['code' => $code])
        ->assertOk();

    $codes = $response->json('data.recovery_codes');
    expect($codes)->toBeArray()
        ->and(count($codes))->toBe(8);

    $fresh = $user->fresh();
    expect($fresh->two_factor_confirmed_at)->not->toBeNull();
    expect($fresh->getRecoveryCodes())->toHaveCount(8);
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

// ─── Recovery Codes Management ────────────────────────────────────────────────

it('user can view recovery codes with current password', function (): void {
    $user = User::factory()->create([
        'password'                  => bcrypt('my-password'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['CODE1-11111', 'CODE2-22222']),
    ]);

    $res = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/recovery-codes', ['password' => 'my-password'])
        ->assertOk();

    expect($res->json('data.recovery_codes'))->toEqual(['CODE1-11111', 'CODE2-22222']);
});

it('viewing recovery codes fails with wrong password', function (): void {
    $user = User::factory()->create([
        'password'                  => bcrypt('my-password'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['CODE1-11111']),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/recovery-codes', ['password' => 'wrong-pass'])
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'PASSWORD_MISMATCH']);
});

it('user can regenerate recovery codes with current password', function (): void {
    $user = User::factory()->create([
        'password'                  => bcrypt('my-password'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['OLD1-11111', 'OLD2-22222']),
    ]);

    $res = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson('/api/v1/auth/two-factor/recovery-codes/regenerate', ['password' => 'my-password'])
        ->assertOk();

    $newCodes = $res->json('data.recovery_codes');
    expect($newCodes)->toBeArray()
        ->and(count($newCodes))->toBe(8)
        ->and($newCodes)->not->toContain('OLD1-11111');

    expect($user->fresh()->getRecoveryCodes())->toEqual($newCodes);
});

// ─── DELETE /auth/two-factor ──────────────────────────────────────────────────

it('user can disable 2FA with correct password', function (): void {
    $user = User::factory()->create([
        'password'                  => bcrypt('secret'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['CODE1-11111']),
    ]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson('/api/v1/auth/two-factor', ['password' => 'secret'])
        ->assertNoContent();

    expect($user->fresh()->two_factor_secret)->toBeNull();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
    expect($user->fresh()->two_factor_recovery_codes)->toBeNull();
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

// ─── LOGIN FLOW & 2FA VERIFICATION ────────────────────────────────────────────

it('user with 2FA enabled receives TWO_FACTOR_REQUIRED on login with a temporary token', function (): void {
    $user = User::factory()->create([
        'email'                   => 'twofactor@example.com',
        'password'                => bcrypt('secret123'),
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $res = $this->postJson('/api/v1/auth/login', [
        'email'    => 'twofactor@example.com',
        'password' => 'secret123',
    ])->assertOk();

    expect($res->json('data.auth_status'))->toBe('TWO_FACTOR_REQUIRED')
        ->and($res->json('data.two_factor_required'))->toBeTrue()
        ->and($res->json('data.token'))->not->toBeNull();
});

it('temporary 2FA token cannot access protected routes', function (): void {
    $user = User::factory()->create([
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $tempToken = $user->createToken('test', ['two-factor:verify'], now()->addMinutes(5))->plainTextToken;

    $this->withToken($tempToken)
        ->getJson('/api/v1/auth/user')
        ->assertStatus(403)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_REQUIRED']);
});

it('user can complete login using a 6-digit TOTP code', function (): void {
    $google2fa = new Google2FA();
    $secret    = $google2fa->generateSecretKey(32);

    $user = User::factory()->create([
        'email'                   => 'twofactor2@example.com',
        'password'                => bcrypt('password'),
        'two_factor_secret'       => encrypt($secret),
        'two_factor_confirmed_at' => now(),
    ]);

    $loginRes = $this->postJson('/api/v1/auth/login', [
        'email'    => 'twofactor2@example.com',
        'password' => 'password',
    ])->assertOk();

    $tempToken = $loginRes->json('data.token');
    $code      = $google2fa->getCurrentOtp($secret);

    $verifyRes = $this->withToken($tempToken)
        ->postJson('/api/v1/auth/two-factor/verify', ['code' => $code])
        ->assertOk()
        ->assertJsonFragment(['auth_status' => 'AUTHENTICATED'])
        ->assertJsonStructure([
            'data' => ['auth_status', 'two_factor_required', 'user', 'token', 'token_type'],
        ]);

    $fullToken = $verifyRes->json('data.token');

    $this->withToken($fullToken)
        ->getJson('/api/v1/auth/user')
        ->assertOk();
});

it('user can complete login using a valid recovery code (one-time use)', function (): void {
    $user = User::factory()->create([
        'email'                     => 'recovery@example.com',
        'password'                  => bcrypt('password'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['CODE1-11111', 'CODE2-22222']),
    ]);

    $loginRes = $this->postJson('/api/v1/auth/login', [
        'email'    => 'recovery@example.com',
        'password' => 'password',
    ])->assertOk();

    $tempToken = $loginRes->json('data.token');

    $verifyRes = $this->withToken($tempToken)
        ->postJson('/api/v1/auth/two-factor/verify', ['code' => 'CODE1-11111'])
        ->assertOk()
        ->assertJsonFragment(['auth_status' => 'AUTHENTICATED']);

    expect($user->fresh()->getRecoveryCodes())->toEqual(['CODE2-22222']);

    // Attempting to reuse the same recovery code fails
    $loginRes2 = $this->postJson('/api/v1/auth/login', [
        'email'    => 'recovery@example.com',
        'password' => 'password',
    ])->assertOk();

    $this->withToken($loginRes2->json('data.token'))
        ->postJson('/api/v1/auth/two-factor/verify', ['code' => 'CODE1-11111'])
        ->assertStatus(422)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_INVALID_CODE']);
});

// ─── 2FA RECOVERY CODES & RESET FLOWS ─────────────────────────────────────────

it('user can reset 2FA using a recovery code and password', function (): void {
    $user = User::factory()->create([
        'email'                     => 'reset2fa@example.com',
        'password'                  => bcrypt('secretpassword'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['VALID-12345', 'OTHER-67890']),
    ]);

    $this->postJson('/api/v1/auth/two-factor/reset', [
        'email'         => 'reset2fa@example.com',
        'password'      => 'secretpassword',
        'recovery_code' => 'VALID-12345',
    ])->assertOk();

    $fresh = $user->fresh();
    expect($fresh->two_factor_secret)->toBeNull()
        ->and($fresh->two_factor_confirmed_at)->toBeNull()
        ->and($fresh->two_factor_recovery_codes)->toBeNull();
});

it('reset 2FA with recovery code rejects invalid recovery code', function (): void {
    $user = User::factory()->create([
        'email'                     => 'resetfail@example.com',
        'password'                  => bcrypt('secretpassword'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['VALID-12345']),
    ]);

    $this->postJson('/api/v1/auth/two-factor/reset', [
        'email'         => 'resetfail@example.com',
        'password'      => 'secretpassword',
        'recovery_code' => 'WRONG-99999',
    ])->assertStatus(422)
        ->assertJsonFragment(['code' => 'TWO_FACTOR_INVALID_RECOVERY_CODE']);
});

it('user can request a 2FA reset link sent to their email and reset via signed URL', function (): void {
    Notification::fake();

    $user = User::factory()->create([
        'email'                     => 'forgot2fa@example.com',
        'password'                  => bcrypt('my-secret-password'),
        'two_factor_secret'         => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at'   => now(),
        'two_factor_recovery_codes' => encrypt(['CODE-12345']),
    ]);

    // 1. Request reset link
    $this->postJson('/api/v1/auth/two-factor/send-reset-link', [
        'email' => 'forgot2fa@example.com',
    ])->assertOk();

    Notification::assertSentTo(
        $user,
        \App\Notifications\TwoFactorResetLinkNotification::class,
        function ($notification) use ($user) {
            return ! empty($notification->resetUrl);
        }
    );

    // 2. Generate signed URL for testing the completion step
    $signedUrl = URL::temporarySignedRoute(
        'auth.2fa.reset-via-link',
        now()->addMinutes(15),
        [
            'id'   => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    // 3. Submit reset with password
    $this->postJson($signedUrl, [
        'password' => 'my-secret-password',
    ])->assertOk();

    $fresh = $user->fresh();
    expect($fresh->two_factor_secret)->toBeNull()
        ->and($fresh->two_factor_confirmed_at)->toBeNull()
        ->and($fresh->two_factor_recovery_codes)->toBeNull();
});
