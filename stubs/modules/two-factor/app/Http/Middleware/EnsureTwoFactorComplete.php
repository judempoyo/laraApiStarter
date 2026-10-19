<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject requests made with a temporary two-factor token.
 *
 * Temporary tokens are created during login when the user has 2FA enabled.
 * They carry only the 'two-factor:verify' ability and must not be used
 * to access any other protected route.
 */
class EnsureTwoFactorComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (
            $request->routeIs('*.2fa.verify')
            || $request->routeIs('auth.2fa.verify')
            || $request->is('*/two-factor/verify')
            || $request->is('*/security/two-factor/verify')
        ) {
            return $next($request);
        }

        if ($token && method_exists($token, 'can') && $token->can('two-factor:verify') && ! $token->can('*')) {
            return ApiResponse::error(
                error_code: ErrorCode::TWO_FACTOR_REQUIRED,
                error_message: __('api.2fa_required') ?: 'Two-factor authentication verification is required to complete login.',
                code: 403,
                message: __('api.2fa_required') ?: 'Two-factor verification pending'
            );
        }

        return $next($request);
    }
}
