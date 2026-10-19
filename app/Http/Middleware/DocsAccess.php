<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Gate::allows('viewApiDocs'), 403);
        $response = $next($request);
        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $nonce = base64_encode(random_bytes(24));
            $response->setContent(preg_replace('/<(script|style)(\s|>)/i', '<$1 nonce="'.$nonce.'"$2', $response->getContent()));
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'self'");
        }

        return $response;
    }
}
