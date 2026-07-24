<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DisableTwoFactorAction
{
    /**
     * Disable 2FA after verifying the user's password.
     */
    public function execute(User $user, string $password): array
    {
        if (! $user->two_factor_confirmed_at) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! Hash::check($password, $user->password)) {
            return ['status' => TwoFactorResult::INVALID_PASSWORD];
        }

        $user->update([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ]);

        return ['status' => TwoFactorResult::DISABLED];
    }
}
