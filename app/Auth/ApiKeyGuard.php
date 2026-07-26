<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class ApiKeyGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(protected Request $request) {}

    /**
     * Resolve the raw API key from the request.
     * Supports X-API-Key / api-key headers and Authorization (Bearer or plain).
     */
    protected function resolveRawKey(): ?string
    {
        // 1. Dedicated headers (highest priority)
        $key = $this->request->header('X-API-Key')
            ?? $this->request->header('x-api-key')
            ?? $this->request->header('api-key')
            ?? $this->request->header('Api-Key');

        if ($key) {
            return $key;
        }

        // 2. Authorization header — Bearer or plain token
        $authHeader = $this->request->header('Authorization');
        if ($authHeader) {
            return str_starts_with($authHeader, 'Bearer ')
                ? substr($authHeader, 7)
                : $authHeader;
        }

        return null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $rawKey = $this->resolveRawKey();

        if (! $rawKey) {
            return null;
        }

        $hashed = hash('sha256', $rawKey);

        $apiKey = ApiKey::where('key', $hashed)
            ->with('user')
            ->first();

        if (! $apiKey || $apiKey->isExpired()) {
            return null;
        }

        $apiKey->update(['last_used_at' => now()]);

        $this->user = $apiKey->user;

        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false; // Not applicable for token-based guards
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }
}
