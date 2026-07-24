<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Responses\ApiResponse;

class RequestSizeLimitMiddleware
{
    /**
     * Reject requests whose body exceeds the configured size limit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxKb = (int) config('api.request.max_size_kb', 10240);
        $contentLength = (int) $request->header('Content-Length', 0);

        if ($contentLength > $maxKb * 1024) {
            return ApiResponse::error(
                ErrorCode::REQUEST_TOO_LARGE,
                "Request body exceeds the maximum allowed size of {$maxKb} KB.",
                413,
                'Payload too large.'
            );
        }

        return $next($request);
    }
}
