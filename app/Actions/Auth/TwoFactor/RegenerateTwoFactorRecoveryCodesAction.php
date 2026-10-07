<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Actions\Security\LogSecurityEventAction;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegenerateTwoFactorRecoveryCodesAction
{
    public function __construct(
        private readonly GenerateTwoFactorRecoveryCodesAction $generateCodesAction
    ) {}

    public function execute(User $user, string $password): array
    {
        if (! $user->hasTwoFactorEnabled()) {
            return ['status' => TwoFactorResult::NOT_ENABLED];
        }

        if (! Hash::check($password, $user->password)) {
            return ['status' => TwoFactorResult::INVALID_PASSWORD];
        }

        $codes = $this->generateCodesAction->execute();
        $user->replaceRecoveryCodes($codes);

        app(LogSecurityEventAction::class)->execute(
            $user,
            '2fa_recovery_codes_regenerated'
        );

        return [
            'status'         => TwoFactorResult::RECOVERY_CODES_REGENERATED,
            'recovery_codes' => $codes,
        ];
    }
}
