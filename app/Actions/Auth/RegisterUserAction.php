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
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        $this->logActivity('auth.register', "User {$user->email} registered.", $user->id);

         if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        $token = $user->createToken('auth_token')->plainTextToken;


        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
