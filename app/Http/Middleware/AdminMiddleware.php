<?php
namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            return ApiResponse::error(
                ErrorCode::NEED_TO_BE_ADMIN,
                'User is not an admin',
                403,
                'Access denied'
            );
        }

        return $next($request);
    }
}
