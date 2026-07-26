<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuth
{
    /**
     * Try to resolve the authenticated user from the configured auth driver,
     * but never block the request if the token is missing or invalid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            $guard = config('api.auth_guard', 'sanctum');

            Auth::guard($guard)->setRequest($request);
            $user = Auth::guard($guard)->user();

            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn () => $user);
            }
        }

        return $next($request);
    }
}
