<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\TokenServiceInterface;
use App\Models\User;

class SanctumTokenService implements TokenServiceInterface
{
    public function createToken(User $user, string $name, array $abilities = ['*']): string
    {
        $tokenInstance = $user->createToken($name, $abilities);

        return $tokenInstance->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function revokeTokenById(User $user, int $tokenId): bool
    {
        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        return $deleted > 0;
    }

    public function revokeOtherTokens(User $user): void
    {
        $current = $user->currentAccessToken()->id;

        $user->tokens()->where('id', '!=', $current)->delete();
    }

    public function getTokenExpiry(): ?string
    {
        $expiration = config('sanctum.expiration');

        return $expiration
            ? now()->addMinutes($expiration)->toIso8601String()
            : null;
    }

    public function guardName(): string
    {
        return 'sanctum';
    }
}
