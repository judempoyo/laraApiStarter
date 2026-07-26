<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class VerifyTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Verify a TOTP code during the login flow.
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
        $valid  = $this->google2fa->verifyKey($secret, $code);

        if (! $valid) {
            return ['status' => TwoFactorResult::INVALID_CODE];
        }

        return ['status' => TwoFactorResult::VERIFIED];
    }
}
