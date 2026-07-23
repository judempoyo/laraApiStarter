<?php
namespace App\Actions\Auth;

use App\DTOs\Auth\UpdateEmailDTO;
use App\Models\User;

class UpdateEmailAction
{
    use \App\Traits\LogsActivity;

    public function execute(User $user, UpdateEmailDTO $dto): array
    {
        $user->update([
            'email' => $dto->email,
        ]);

        $user->email_verified_at === null;
        $user->sendEmailVerificationNotification();

        $this->logActivity('auth.email.updated', "User email updated.", $user->id);

        return [
            'status' => \App\Enums\Result\Auth\UpdateEmailResult::SUCCESS,
            'user'   => $user,
        ];
    }
}
