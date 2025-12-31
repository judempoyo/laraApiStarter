<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\UpdatePasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    use \App\Traits\LogsActivity;

    public function execute(User $user, UpdatePasswordDTO $dto): void
    {
        if (! Hash::check($dto->currentPassword, $user->password)) {
            $this->logActivity('auth.password.update_failed', "Failed password update (incorrect current password).", $user->id);
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($dto->newPassword),
            'password_updated_at' => now(),
        ]);

        $this->logActivity('auth.password.updated', "User password updated.", $user->id);
    }
}
