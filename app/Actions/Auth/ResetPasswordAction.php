<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    use \App\Traits\LogsActivity;

    /**
     * Reset the given user's password.
     *
     * @param  array  $credentials
     * @return array
     */
    public function execute(array $credentials): array
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'password_updated_at' => now(),
                ])->save();

                $this->logActivity('auth.password.reset', "User password reset successful.", $user->id);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return ['status' => \App\Enums\Auth\PasswordResetStatus::INVALID_TOKEN];
        }

        return ['status' => \App\Enums\Auth\PasswordResetStatus::RESET_SUCCESS];
    }
}
