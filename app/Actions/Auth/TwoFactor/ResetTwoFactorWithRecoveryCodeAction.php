<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Actions\Security\LogSecurityEventAction;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetTwoFactorWithRecoveryCodeAction
{
    /**
     * Reset/disable 2FA using a recovery code and password confirmation.
     */
    public function execute(string $email, string $password, string $recoveryCode): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! $user->hasTwoFactorEnabled()) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! Hash::check($password, $user->password)) {
            return ['status' => TwoFactorResult::INVALID_PASSWORD];
        }

        $valid = $user->useRecoveryCode($recoveryCode);

        if (! $valid) {
            return ['status' => TwoFactorResult::INVALID_RECOVERY_CODE];
        }

        $user->resetTwoFactor();
        $user->tokens()->delete();

        app(LogSecurityEventAction::class)->execute(
            $user,
            '2fa_reset_via_recovery_code'
        );

        return [
            'status' => TwoFactorResult::RESET_SUCCESS,
            'user'   => $user,
        ];
    }
}
