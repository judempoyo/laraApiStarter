<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\UpdateProfileDTO;
use App\Enums\Result\Auth\UpdateProfileResult;
use App\Events\ActivityRecorded;
use App\Models\User;

class UpdateProfileAction
{
    public function execute(User $user, UpdateProfileDTO $dto): array
    {
        $user->update([
            'name' => $dto->name,
        ]);

        ActivityRecorded::dispatch('auth.profile.updated', 'User profile updated.', $user->id);

        return [
            'status' => UpdateProfileResult::SUCCESS,
            'user' => $user,
        ];
    }
}
