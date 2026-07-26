<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class EnableTwoFactorAction
{
    public function __construct(private readonly Google2FA $google2fa) {}

    /**
     * Generate a new 2FA secret and return the QR code URI.
     * The secret is stored but 2FA is not yet active until confirmed.
     */
    public function execute(User $user): array
    {
        if ($user->two_factor_confirmed_at !== null) {
            return ['status' => TwoFactorResult::ALREADY_ENABLED];
        }

        $secret = $this->google2fa->generateSecretKey(32);

        $user->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_confirmed_at' => null,
        ]);

        $qrCodeUri = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'status'     => TwoFactorResult::ENABLED,
            'secret'     => $secret,
            'qr_code_uri' => $qrCodeUri,
        ];
    }
}
