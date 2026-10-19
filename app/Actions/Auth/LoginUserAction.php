<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Auth\TokenServiceInterface;
use App\DTOs\Auth\LoginDTO;
use App\Enums\Result\Auth\LoginResult;
use App\Enums\UserStatus;
use App\Events\LoginChallenged;
use App\Events\SecurityEventOccurred;
use App\Events\UserAuthenticated;
use App\Events\UserLoggedIn;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    public function execute(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            SecurityEventOccurred::dispatch(
                $user ?? null,
                'LOGIN_FAILED'
            );

            return [
                'status' => LoginResult::INVALID_CREDENTIALS,
            ];
        }

        if ($user->status === UserStatus::DISABLED) {
            return [
                'status' => LoginResult::USER_DISABLED,
            ];
        }

        UserAuthenticated::dispatch($user);
        $currentDevice = app(ResolveDeviceNameAction::class)->execute();

        $challenge = new LoginChallenged($user, $currentDevice);
        event($challenge);
        if ($challenge->result !== null) {
            return $challenge->result;
        }

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $plainToken = $tokenService->createToken($user, $currentDevice);

        UserLoggedIn::dispatch($user);

        return [
            'status' => LoginResult::SUCCESS,
            'user' => $user,
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenService->getTokenExpiry(),
        ];
    }
}
