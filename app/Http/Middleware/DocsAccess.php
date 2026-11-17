<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class DocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Gate::allows('viewApiDocs'), 403);
        $nonce = Vite::cspNonce() ?? Vite::useCspNonce();
        $response = $next($request);
        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->setContent(preg_replace('/<(script|style)(?![^>]*\bnonce=)(\s|>)/i', '<$1 nonce="'.$nonce.'"$2', $response->getContent()));
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https: 'nonce-{$nonce}'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https:",
                "font-src 'self' https://fonts.gstatic.com data: https:",
                "img-src 'self' data: https:",
                "connect-src * 'self' data: https:",
                "frame-ancestors 'none'",
                "object-src 'none'",
                "base-uri 'self'",
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}
