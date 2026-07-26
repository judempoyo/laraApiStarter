<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\TokenServiceInterface;
use App\Models\User;

/**
 * Passport implementation of the TokenServiceInterface.
 *
 * Prerequisites:
 *   1. Install the package:   composer require laravel/passport
 *   2. Run:                   php artisan passport:install
 *   3. In User.php, replace:  Laravel\Sanctum\HasApiTokens
 *      with:                  Laravel\Passport\HasApiTokens
 *   4. In .env, set:
 *        AUTH_DRIVER=passport
 *        AUTH_GUARD=api
 *   5. In config/auth.php, ensure 'api' guard uses driver "passport".
 */
class PassportTokenService implements TokenServiceInterface
{
    public function createToken(User $user, string $name, array $abilities = ['*']): string
    {
        /** @var \Laravel\Passport\PersonalAccessTokenResult $result */
        $result = $user->createToken($name, $abilities);

        return $result->accessToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $tokenId = $user->token()?->id;

        if ($tokenId) {
            \Laravel\Passport\Token::find($tokenId)?->revoke();
        }
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->each(function (\Laravel\Passport\Token $token): void {
            $token->revoke();
        });
    }

    public function revokeTokenById(User $user, int $tokenId): bool
    {
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            return false;
        }

        $token->revoke();

        return true;
    }

    public function revokeOtherTokens(User $user): void
    {
        $currentId = $user->token()?->id;

        $user->tokens()
            ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
            ->each(fn (\Laravel\Passport\Token $t) => $t->revoke());
    }

    public function getTokenExpiry(): ?string
    {
        // Passport uses oauth2 grants; personal access tokens do not expire by default.
        // Customize this if you configure expiration in AuthServiceProvider::boot().
        return null;
    }

    public function guardName(): string
    {
        return 'api';
    }
}
