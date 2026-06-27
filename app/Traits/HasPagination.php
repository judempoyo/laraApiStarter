<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;

trait HasPagination
{
    /**
     * Get the number of items per page from the request, falling back to a default.
     * Enforces a maximum limit to prevent abuse.
     */
    protected function getPerPage(Request $request, int $default = 15, int $max = 100): int
    {
        $perPage = (int) $request->query('per_page', $default);

        if ($perPage < 1) {
            return $default;
        }

        return min($perPage, $max);
    }
}
