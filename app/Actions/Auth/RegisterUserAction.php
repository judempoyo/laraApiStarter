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
            'password_updated_at' => now(),
        ]);

        $this->logActivity('auth.register', "User {$user->email} registered.", $user->id);

        dispatch(fn () => $user->sendEmailVerificationNotification())
            ->afterResponse();

        $tokenInstance = $user->createToken('auth_token');
        $token = $tokenInstance->plainTextToken;

        $expiration = config('sanctum.expiration');
        $expiresAt = $expiration ? now()->addMinutes($expiration)->toIso8601String() : null;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ];
    }
}
