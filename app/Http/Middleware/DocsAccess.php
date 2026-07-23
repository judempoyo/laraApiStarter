<?php
namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DocsAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::allows('viewApiDocs') && Gate::allows('viewScalar')) {
            return $next($request);
        }

        return ApiResponse::error(
            ErrorCode::UNAUTHORIZED,
            error_message: "You do not have the necessary permissions to access this documentation.",
            code: 403,
            message: 'Access denied'
        );
    }
}
