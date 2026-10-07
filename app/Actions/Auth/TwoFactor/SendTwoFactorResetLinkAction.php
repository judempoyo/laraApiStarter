<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use App\Actions\Security\LogSecurityEventAction;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Models\User;
use App\Notifications\TwoFactorResetLinkNotification;
use Illuminate\Support\Facades\URL;

class SendTwoFactorResetLinkAction
{
    /**
     * Send a temporary signed 2FA reset link to the user's verified email.
     */
    public function execute(string $email, string $routeName = 'auth.2fa.reset-via-link'): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return [
                'status'  => TwoFactorResult::RESET_LINK_SENT,
                'message' => __('api.2fa_reset_link_sent_msg') ?: 'If an account matching that email has 2FA enabled, a reset link has been sent.',
            ];
        }

        $expiresAt = now()->addMinutes(15);

        $signedUrl = URL::temporarySignedRoute(
            $routeName,
            $expiresAt,
            [
                'id'   => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $query = [];
        parse_str(parse_url($signedUrl, PHP_URL_QUERY) ?? '', $query);

        $frontendBase = rtrim((string) (config('app.frontend_url') ?? config('app.url')), '/');
        $frontendResetUrl = $frontendBase . '/auth/two-factor/reset-confirm?' . http_build_query([
            'id'        => $user->id,
            'hash'      => sha1($user->getEmailForVerification()),
            'signature' => $query['signature'] ?? '',
            'expires'   => $expiresAt->timestamp,
        ]);

        $user->notify(new TwoFactorResetLinkNotification($frontendResetUrl));

        app(LogSecurityEventAction::class)->execute(
            $user,
            '2fa_reset_link_requested'
        );

        return [
            'status'  => TwoFactorResult::RESET_LINK_SENT,
            'message' => __('api.2fa_reset_link_sent') ?: 'A secure 2FA reset link has been sent to your email address.',
        ];
    }
}
