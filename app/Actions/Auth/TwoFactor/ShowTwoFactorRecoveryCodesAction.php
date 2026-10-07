<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ShowTwoFactorRecoveryCodesAction
{
    public function execute(User $user, string $password): array
    {
        if (! $user->hasTwoFactorEnabled()) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! Hash::check($password, $user->password)) {
            return ['status' => TwoFactorResult::INVALID_PASSWORD];
        }

        return [
            'status'         => TwoFactorResult::RECOVERY_CODES_RETRIEVED,
            'recovery_codes' => $user->getRecoveryCodes(),
        ];
    }
}
