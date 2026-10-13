<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Events\SecurityEventOccurred;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetTwoFactorViaSignedLinkAction
{
    /**
     * Verify password and reset 2FA via a verified signed request.
     */
    public function execute(User $user, string $password): array
    {
        if (! $user->hasTwoFactorEnabled()) {
            return [
                'status' => TwoFactorResult::NOT_ENABLED,
                'message' => __('modules/two-factor.2fa_not_enabled') ?: 'Two-factor authentication is not enabled.',
            ];
        }

        if (! Hash::check($password, $user->password)) {
            return [
                'status' => TwoFactorResult::INVALID_PASSWORD,
                'message' => __('modules/two-factor.2fa_invalid_password') ?: 'The provided password is incorrect.',
            ];
        }

        $user->resetTwoFactor();
        $user->tokens()->delete();

        SecurityEventOccurred::dispatch(
            $user,
            '2fa_reset_via_email_link'
        );

        return [
            'status' => TwoFactorResult::RESET_SUCCESS,
            'user' => $user->fresh(),
            'message' => __('modules/two-factor.2fa_reset_success') ?: 'Two-factor authentication has been successfully reset.',
        ];
    }
}
