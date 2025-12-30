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
     * @return string
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(array $credentials): string
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
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }
}
