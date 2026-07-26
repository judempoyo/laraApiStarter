<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiKey
{
    /**
     * Enforce API Key authentication (X-API-Key header).
     *
     * This middleware delegates all authentication logic to ApiKeyGuard
     * — the single source of truth for API key resolution and validation.
     *
     * ┌─────────────────────────────────────────────────────────────────┐
     * │  Use 'api.key' middleware when you want to restrict a route     │
     * │  exclusively to API keys (no Bearer tokens accepted).           │
     * │                                                                 │
     * │  To accept BOTH Sanctum Bearer tokens AND API keys, use:       │
     * │    middleware("auth:{$guard},api-key")                          │
     * └─────────────────────────────────────────────────────────────────┘
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('api-key');

        $user = $guard->user();

        if (! $user) {
            throw ApiException::unauthorized('API key is missing or invalid.');
        }

        // Expose the resolved user to the rest of the request lifecycle.
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
