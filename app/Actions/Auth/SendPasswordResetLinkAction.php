<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\Result\Auth\PasswordResetResult;
use App\Events\ActivityRecorded;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetLinkAction
{
    /**
     * Send a password reset link to the given user.
     *
     * @return string
     *
     * @throws ValidationException
     */
    public function execute(array $credentials): array
    {
        $status = Password::sendResetLink($credentials);

        if ($status !== Password::RESET_LINK_SENT) {
            ActivityRecorded::dispatch('auth.password.forgot_failed', "Failed password reset link request for: {$credentials['email']}");
        } else {
            ActivityRecorded::dispatch('auth.password.forgot', "Password reset link sent to: {$credentials['email']}");
        }

        return match ($status) {
            Password::RESET_LINK_SENT => ['status' => PasswordResetResult::LINK_SENT],
            Password::INVALID_USER => ['status' => PasswordResetResult::INVALID_USER],
            Password::RESET_THROTTLED => ['status' => PasswordResetResult::THROTTLED],
            default => ['status' => PasswordResetResult::INVALID_USER],
        };
    }
}
