<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\Auth\LoginStatus;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    use \App\Traits\LogsActivity;

    public function execute(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            $this->logActivity('auth.login.failed', "Failed login attempt for email: {$dto->email}");
            return ['status' => LoginStatus::INVALID_CREDENTIALS];
        }

        $this->logActivity('auth.login', "User logged in.", $user->id);

        $user->load(['roles', 'permissions']);

        $tokenInstance = $user->createToken('auth_token');
        $token = $tokenInstance->plainTextToken;

        $expiration = config('sanctum.expiration');
        $expiresAt = $expiration ? now()->addMinutes($expiration)->toIso8601String() : null;

        return [
            'status' => LoginStatus::SUCCESS,
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt,
            ]
        ];
    }
}
