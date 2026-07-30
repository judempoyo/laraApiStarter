<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait AppliesExportFilters
 *
 * Provides a reusable method to apply common export filters
 * (dates, ID range, specific IDs) to any Eloquent query.
 * Import this trait into any ExportableInterface implementation.
 */
trait AppliesExportFilters
{
    /**
     * Apply all supported generic filters to an Eloquent builder.
     *
     * Supported filter keys:
     *   - ids      (array<int>)  Specific IDs to include.
     *   - id_from  (int)         Minimum ID (inclusive).
     *   - id_to    (int)         Maximum ID (inclusive).
     *   - date_from (string)     Lower bound on created_at (ISO 8601).
     *   - date_to  (string)      Upper bound on created_at (ISO 8601).
     *
     * @param  Builder             $query
     * @param  array<string, mixed> $filters
     * @param  string              $idColumn     Column to use for ID filtering (default: 'id').
     * @param  string              $dateColumn   Column to use for date filtering (default: 'created_at').
     * @return Builder
     */
    protected function applyFilters(
        Builder $query,
        array   $filters,
        string  $idColumn   = 'id',
        string  $dateColumn = 'created_at',
    ): Builder {
        // Specific IDs — overrides id_from/id_to when present.
        if (! empty($filters['ids'])) {
            return $query->whereIn($idColumn, (array) $filters['ids']);
        }

        // ID range
        if (isset($filters['id_from'])) {
            $query->where($idColumn, '>=', (int) $filters['id_from']);
        }

        if (isset($filters['id_to'])) {
            $query->where($idColumn, '<=', (int) $filters['id_to']);
        }

        // Date range
        if (isset($filters['date_from'])) {
            $query->where($dateColumn, '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where($dateColumn, '<=', $filters['date_to']);
        }

        return $query;
    }
}
