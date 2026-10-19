<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\Result\Auth\LoginResult;
use App\Events\ActivityRecorded;
use App\Events\LoginChallenged;
use App\Events\UserAuthenticated;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialiteLoginAction
{
    public function execute(SocialiteUser $socialUser, string $provider = 'google'): array
    {
        /** @var User $user */
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'google_id' => $provider === 'google' ? $socialUser->getId() : null,
                ...(method_exists(User::class, 'initializeHasMedia') ? ['avatar' => $socialUser->getAvatar()] : []),
                'email_verified_at' => now(),
            ]
        );

        UserRegistered::dispatch($user);
        UserAuthenticated::dispatch($user);

        $deviceName = app(ResolveDeviceNameAction::class)->execute();

        $challenge = new LoginChallenged($user, $deviceName);
        event($challenge);
        if ($challenge->result !== null) {
            return $challenge->result;
        }

        $tokenInstance = $user->createToken($deviceName);

        UserLoggedIn::dispatch($user);

        ActivityRecorded::dispatch('auth.social_login', "User logged in via {$provider}.", $user->id);

        $expiration = config('sanctum.expiration');

        return [
            'status' => LoginResult::SUCCESS,
            'user' => $user,
            'token' => $tokenInstance->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration
                ? now()->addMinutes($expiration)->toIso8601String()
                : null,
        ];
    }
}
