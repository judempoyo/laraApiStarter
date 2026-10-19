<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\Result\Auth\PasswordResetResult;
use App\Events\ActivityRecorded;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    /**
     * Reset the given user's password.
     *
     *
     * @throws ValidationException
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

                ActivityRecorded::dispatch('auth.password.reset', 'User password reset successful.', $user->id);
            }
        );

        return match ($status) {
            Password::PASSWORD_RESET => ['status' => PasswordResetResult::RESET_SUCCESS],
            Password::INVALID_TOKEN => ['status' => PasswordResetResult::INVALID_TOKEN],
            Password::INVALID_USER => ['status' => PasswordResetResult::INVALID_USER],
            Password::RESET_THROTTLED => ['status' => PasswordResetResult::THROTTLED],
            default => ['status' => PasswordResetResult::INVALID_TOKEN],
        };
    }
}
