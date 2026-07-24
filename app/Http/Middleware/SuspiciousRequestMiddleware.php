<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Responses\ApiResponse;

class SuspiciousRequestMiddleware
{
    /**
     * Inspect request inputs and headers for common attack patterns.
     * Matching requests are rejected with a 400 and logged as a warning.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $patterns = config('api.security.suspicious_patterns', []);

        if (!empty($patterns) && $this->isSuspicious($request, $patterns)) {
            Log::warning('Suspicious request blocked', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'path' => $request->path(),
                'agent' => $request->userAgent(),
            ]);

            return ApiResponse::error(
                ErrorCode::SUSPICIOUS_REQUEST,
                'Request contains disallowed content.',
                400,
                'Bad request.'
            );
        }

        return $next($request);
    }

    private function isSuspicious(Request $request, array $patterns): bool
    {
        $targets = array_merge(
            [$request->path(), $request->userAgent() ?? ''],
            array_values($request->query()),
            array_values($request->except(['password', 'password_confirmation', 'current_password'])),
        );

        foreach ($targets as $value) {
            if (!is_string($value)) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (stripos($value, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
