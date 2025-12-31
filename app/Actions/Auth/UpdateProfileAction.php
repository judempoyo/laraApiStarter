<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\UpdateProfileDTO;
use App\Models\User;

class UpdateProfileAction
{
    use \App\Traits\LogsActivity;

    public function execute(User $user, UpdateProfileDTO $dto): User
    {
        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        $this->logActivity('auth.profile.updated', "User profile updated.", $user->id);

        return $user;
    }
}
