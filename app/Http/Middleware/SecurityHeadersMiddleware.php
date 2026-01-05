<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
    $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
    $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    $response->headers->set('X-Download-Options', 'noopen');

        $csp = [
            "default-src 'self'",
            "script-src 'self' https://unpkg.com 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https://unpkg.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp) . ';');

    return $response;
}

}
