<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\UpdateEmailDTO;
use App\Enums\Result\Auth\UpdateEmailResult;
use App\Events\ActivityRecorded;
use App\Models\User;

class UpdateEmailAction
{
    public function execute(User $user, UpdateEmailDTO $dto): array
    {
        $user->update([
            'email' => $dto->email,
        ]);

        $user->email_verified_at = null;
        $user->sendEmailVerificationNotification();

        ActivityRecorded::dispatch('auth.email.updated', 'User email updated.', $user->id);

        return [
            'status' => UpdateEmailResult::SUCCESS,
            'user' => $user,
        ];
    }
}
