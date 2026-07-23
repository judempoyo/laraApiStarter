<?php

namespace App\Actions\Auth;

use App\Enums\Result\Auth\CheckEmailResult;
use App\Models\User;

class CheckEmailAction
{
    public function execute(string $email): array
    {
        $exists = User::where('email', $email)->exists();

        return [
            'status' => $exists ? CheckEmailResult::EXISTS : CheckEmailResult::NOT_FOUND,
            'exists' => $exists,
        ];
    }
}
