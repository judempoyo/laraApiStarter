<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\UpdatePasswordDTO;
use App\Enums\Result\Auth\UpdatePasswordResult;
use App\Events\ActivityRecorded;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction
{
    public function execute(User $user, UpdatePasswordDTO $dto): array
    {
        if (! Hash::check($dto->currentPassword, $user->password)) {
            ActivityRecorded::dispatch('auth.password.update_failed', 'Failed password update (incorrect current password).', $user->id);

            return ['status' => UpdatePasswordResult::INVALID_CURRENT_PASSWORD];
        }

        $user->update([
            'password' => Hash::make($dto->newPassword),
            'password_updated_at' => now(),
        ]);

        ActivityRecorded::dispatch('auth.password.updated', 'User password updated.', $user->id);

        return ['status' => UpdatePasswordResult::SUCCESS];
    }
}
