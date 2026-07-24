<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Exceptions\ApiException;
use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiKey
{
    /**
     * Authenticate a request using the X-API-Key header.
     *
     * Sets the authenticated user on the request so downstream controllers
     * can use $request->user() as usual.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawKey = $request->header('X-API-Key');

        if (! $rawKey) {
            throw ApiException::unauthorized('API key is missing.');
        }

        $hashed = hash('sha256', $rawKey);
        $apiKey = ApiKey::where('key', $hashed)->with('user')->first();

        if (! $apiKey) {
            throw new ApiException(
                errorCode: ErrorCode::API_KEY_INVALID,
                message: 'Invalid API key.',
                statusCode: 401,
                userMessage: 'The provided API key is not valid.',
            );
        }

        if ($apiKey->isExpired()) {
            throw new ApiException(
                errorCode: ErrorCode::API_KEY_EXPIRED,
                message: 'API key has expired.',
                statusCode: 401,
                userMessage: 'Your API key has expired. Please generate a new one.',
            );
        }

        $apiKey->update(['last_used_at' => now()]);

        Auth::setUser($apiKey->user);
        $request->setUserResolver(fn () => $apiKey->user);

        return $next($request);
    }
}
