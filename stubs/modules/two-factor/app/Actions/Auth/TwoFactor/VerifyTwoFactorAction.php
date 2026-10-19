<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Actions\Auth\ResolveDeviceNameAction;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Events\SecurityEventOccurred;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class VerifyTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Verify a TOTP code or recovery code during the login flow.
     * On success, revoke the temporary token and issue a full-access token.
     */
    public function execute(User $user, string $code): array
    {
        if (! $user->two_factor_secret) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! $user->two_factor_confirmed_at) {
            return ['status' => TwoFactorResult::NOT_CONFIRMED];
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $code);

        $usedRecoveryCode = false;
        if (! $valid) {
            $usedRecoveryCode = $user->useRecoveryCode($code);
            if (! $usedRecoveryCode) {
                return ['status' => TwoFactorResult::INVALID_CODE];
            }
        }

        $user->currentAccessToken()?->delete();

        $deviceName = app(ResolveDeviceNameAction::class)->execute();
        $tokenInstance = $user->createToken($deviceName);
        $user->withAccessToken($tokenInstance->accessToken);

        SecurityEventOccurred::dispatch(
            $user,
            $usedRecoveryCode ? '2fa_recovery_code_used' : 'LOGIN_SUCCESS'
        );

        $expiration = config('sanctum.expiration');

        return [
            'status' => TwoFactorResult::VERIFIED,
            'user' => $user,
            'token' => $tokenInstance->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration
                ? now()->addMinutes($expiration)->toIso8601String()
                : null,
        ];
    }
}
