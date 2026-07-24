<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;

/**
 * Resolves a human-readable device/session name from:
 *   1. The explicit `Device-Name` header (highest priority)
 *   2. The `User-Agent` string (parsed automatically)
 *   3. Falls back to 'API' when nothing is recognisable
 */
class ResolveDeviceNameAction
{
    private const PATTERNS = [
        'Android' => '/android/i',
        'iOS'     => '/iphone|ipad|ipod/i',
        'Chrome'  => '/chrome/i',
        'Firefox' => '/firefox/i',
        'Edge'    => '/edg(e|\/)/i',
        'Safari'  => '/safari/i',
    ];

    public function execute(?Request $request = null): string
    {
        $request ??= request();

        $explicit = $request->header('Device-Name');
        if ($explicit && trim($explicit) !== '') {
            return trim($explicit);
        }

        $userAgent = $request->userAgent() ?? '';

        foreach (self::PATTERNS as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return 'API';
    }
}
