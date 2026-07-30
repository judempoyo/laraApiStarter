<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the application locale from the Accept-Language HTTP header.
     *
     * The locale is matched against the list defined in config('app.supported_locales').
     * Falls back to config('app.locale') if no match is found.
     *
     * Example headers:
     *   Accept-Language: fr          → sets locale to 'fr'
     *   Accept-Language: fr-FR,fr;q=0.9,en;q=0.8 → sets locale to 'fr'
     *   Accept-Language: de          → falls back to default locale
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en']);
        $default   = config('app.locale', 'en');

        $header = $request->header('Accept-Language', $default);

        // Parse the Accept-Language header and find the first supported locale.
        $locale = $this->resolveLocale($header, $supported, $default);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Parse the Accept-Language header value and return the best-matching supported locale.
     *
     * @param  string        $header    Raw Accept-Language header value.
     * @param  list<string>  $supported List of supported locale codes.
     * @param  string        $default   Fallback locale code.
     */
    private function resolveLocale(string $header, array $supported, string $default): string
    {
        // Split by comma to handle quality values: "fr-FR,fr;q=0.9,en;q=0.8"
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            // Strip quality value (;q=0.x) and normalize.
            $locale = strtolower(trim(explode(';', $part)[0]));

            // Exact match: "fr"
            if (in_array($locale, $supported, true)) {
                return $locale;
            }

            // Primary language subtag match: "fr-FR" → "fr"
            $primary = explode('-', $locale)[0];
            if (in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return $default;
    }
}
