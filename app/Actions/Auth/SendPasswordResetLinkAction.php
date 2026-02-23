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
     * @return array
     */
    public function execute(array $credentials): array
    {
        $status = Password::sendResetLink($credentials);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->logActivity('auth.password.forgot_failed', "Failed password reset link request for: {$credentials['email']}");
            return ['status' => \App\Enums\Auth\PasswordResetStatus::INVALID_USER];
        }

        $this->logActivity('auth.password.forgot', "Password reset link sent to: {$credentials['email']}");
        return ['status' => \App\Enums\Auth\PasswordResetStatus::LINK_SENT];
    }
}
