<?php
namespace App\Actions\Auth;

use App\DTOs\Auth\UpdatePasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction
{
    use \App\Traits\LogsActivity;

    public function execute(User $user, UpdatePasswordDTO $dto): array
    {
        if (! Hash::check($dto->currentPassword, $user->password)) {
            $this->logActivity('auth.password.update_failed', "Failed password update (incorrect current password).", $user->id);
            return ['status' => \App\Enums\Result\Auth\UpdatePasswordResult::INVALID_CURRENT_PASSWORD];
        }

        $user->update([
            'password'            => Hash::make($dto->newPassword),
            'password_updated_at' => now(),
        ]);

        $this->logActivity('auth.password.updated', "User password updated.", $user->id);
        return ['status' => \App\Enums\Result\Auth\UpdatePasswordResult::SUCCESS];
    }
}
