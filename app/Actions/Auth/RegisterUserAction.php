<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Auth\TokenServiceInterface;
use App\DTOs\Auth\RegisterDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    use \App\Traits\LogsActivity;

    public function execute(RegisterDTO $dto): array
    {
        $user = User::create([
            'name'                => $dto->name,
            'email'               => $dto->email,
            'password'            => Hash::make($dto->password),
            'password_updated_at' => now(),
        ]);

        $this->logActivity('auth.register', "User {$user->email} registered.", $user->id);

        $user->assignRole(UserRole::USER->value);
        $user->load(['roles', 'permissions']);
        $user->sendEmailVerificationNotification();

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $plainToken   = $tokenService->createToken($user, 'auth_token');

        return [
            'user'       => $user,
            'token'      => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenService->getTokenExpiry(),
        ];
    }
}
