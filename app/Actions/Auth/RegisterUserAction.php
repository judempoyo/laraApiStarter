<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    use \App\Traits\LogsActivity;

    public function execute(RegisterDTO $dto): array
    {
        $hashed = Hash::make($dto->password);

        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $hashed,
        ]);

        $this->logActivity('auth.register', "User {$user->email} registered.", $user->id);

        dispatch(fn () => $user->sendEmailVerificationNotification())
            ->afterResponse();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
