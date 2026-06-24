<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuth
{
    /**
     * Try to resolve the authenticated user from a Sanctum token if present,
     * but never block the request if the token is missing or invalid.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            Auth::guard('sanctum')->setRequest($request);
            $user = Auth::guard('sanctum')->user();

            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn() => $user);
            }
        }

        return $next($request);
    }
}
