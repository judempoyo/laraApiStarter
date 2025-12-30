<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetLinkAction
{
    use \App\Traits\LogsActivity;

    /**
     * Send a password reset link to the given user.
     *
     * @param  array  $credentials
     * @return string
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(array $credentials): string
    {
        $status = Password::sendResetLink($credentials);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->logActivity('auth.password.forgot_failed', "Failed password reset link request for: {$credentials['email']}");
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        $this->logActivity('auth.password.forgot', "Password reset link sent to: {$credentials['email']}");

        return $status;
    }
}
