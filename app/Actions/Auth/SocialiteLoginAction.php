<?php
namespace App\Actions\Auth;

use App\Actions\Security\LogSecurityEventAction;
use App\Enums\SecurityEvent;
use App\Enums\UserRole;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialiteLoginAction
{
    use \App\Traits\LogsActivity;

    public function execute(SocialiteUser $socialUser, string $provider = 'google'): array
    {
        /** @var User $user */
        $user = User::updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                'provider'          => $provider,
                'provider_id'       => $socialUser->getId(),
                'google_id'         => $provider === 'google' ? $socialUser->getId() : null,
                'avatar'            => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole(UserRole::USER->value)) {
            $user->assignRole(UserRole::USER->value);
        }

        $user->load(['roles', 'permissions']);

        $deviceName    = app(ResolveDeviceNameAction::class)->execute();
        $tokenInstance = $user->createToken($deviceName);

        app(LogSecurityEventAction::class)->execute(
            $user,
            SecurityEvent::LOGIN_SUCCESS->value
        );

        $this->logActivity('auth.social_login', "User logged in via {$provider}.", $user->id);

        $expiration = config('sanctum.expiration');

        return [
            'user'       => $user,
            'token'      => $tokenInstance->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration
                ? now()->addMinutes($expiration)->toIso8601String()
                : null,
        ];
    }
}
