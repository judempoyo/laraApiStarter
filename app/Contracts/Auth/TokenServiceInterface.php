<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\User;

interface TokenServiceInterface
{
    /**
     * Create a new token for the given user.
     * Returns the plain-text token string.
     */
    public function createToken(User $user, string $name, array $abilities = ['*']): string;

    /**
     * Revoke the current access token for the user.
     */
    public function revokeCurrentToken(User $user): void;

    /**
     * Revoke all tokens for the user.
     */
    public function revokeAllTokens(User $user): void;

    /**
     * Revoke a specific token by its ID.
     * Returns true if the token was found and deleted.
     */
    public function revokeTokenById(User $user, int $tokenId): bool;

    /**
     * Revoke all tokens except the current one.
     */
    public function revokeOtherTokens(User $user): void;

    /**
     * Return the token expiration as an ISO 8601 string, or null if no expiry.
     */
    public function getTokenExpiry(): ?string;

    /**
     * Return the guard name used by this driver (e.g. "sanctum" or "api").
     */
    public function guardName(): string;
}
