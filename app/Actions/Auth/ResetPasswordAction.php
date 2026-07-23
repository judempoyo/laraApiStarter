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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(array $credentials): array
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password'            => Hash::make($password),
                    'remember_token'      => Str::random(60),
                    'password_updated_at' => now(),
                ])->save();

                $this->logActivity('auth.password.reset', "User password reset successful.", $user->id);
            }
        );

        return match ($status) {
            Password::PASSWORD_RESET  => ['status' => \App\Enums\Result\Auth\PasswordResetResult::RESET_SUCCESS],
            Password::INVALID_TOKEN   => ['status' => \App\Enums\Result\Auth\PasswordResetResult::INVALID_TOKEN],
            Password::INVALID_USER    => ['status' => \App\Enums\Result\Auth\PasswordResetResult::INVALID_USER],
            Password::RESET_THROTTLED => ['status' => \App\Enums\Result\Auth\PasswordResetResult::THROTTLED],
            default                   => ['status' => \App\Enums\Result\Auth\PasswordResetResult::INVALID_TOKEN],
        };
    }
}
