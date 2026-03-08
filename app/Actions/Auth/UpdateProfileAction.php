<?php
namespace App\Actions\Auth;

use App\DTOs\Auth\UpdateProfileDTO;
use App\Models\User;

class UpdateProfileAction
{
    use \App\Traits\LogsActivity;

    public function execute(User $user, UpdateProfileDTO $dto): array
    {
        $user->update([
            'name' => $dto->name,
        ]);

        $this->logActivity('auth.profile.updated', "User profile updated.", $user->id);

        return [
            'status' => \App\Enums\Result\Auth\UpdateProfileResult::SUCCESS,
            'user'   => $user,
        ];
    }
}
