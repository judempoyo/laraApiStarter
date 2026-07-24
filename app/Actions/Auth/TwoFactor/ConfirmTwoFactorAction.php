<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class ConfirmTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Validate the first TOTP code and activate 2FA for the user.
     */
    public function execute(User $user, string $code): array
    {
        if (! $user->two_factor_secret || $user->two_factor_confirmed_at !== null) {
            return ['status' => TwoFactorResult::ALREADY_ENABLED];
        }

        $secret = decrypt($user->two_factor_secret);
        $valid  = $this->google2fa->verifyKey($secret, $code);

        if (! $valid) {
            return ['status' => TwoFactorResult::INVALID_CODE];
        }

        $user->update(['two_factor_confirmed_at' => now()]);

        return ['status' => TwoFactorResult::CONFIRMED];
    }
}
