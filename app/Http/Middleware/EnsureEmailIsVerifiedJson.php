<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedJson
{
    /**
     * Handle an incoming request.
     */
  public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if (! $user || ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail())) {
        return ApiResponse::error('Email not verified.', 403);
    }

    return $next($request);
}

}
