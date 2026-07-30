<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

/**
 * Contract for any resource that can be exported.
 *
 * Register implementations in config/export.php under 'resources'.
 *
 * Filters array supports arbitrary key-value pairs:
 *   - 'user_id'    → (int|array<int>) scope to specific user(s) — admin only
 *   - 'date_from'  → (string) ISO 8601 date lower bound
 *   - 'date_to'    → (string) ISO 8601 date upper bound
 *   - 'status'     → (string) filter by status field
 *   - Any other field relevant to the implementing model.
 *
 * When $user is null and isAdminOnly() returns true, the export covers all users.
 * When $user is provided, the export is scoped to that user's data only.
 */
interface ExportableInterface
{
    /**
     * Column headers for CSV/XLSX outputs.
     *
     * @return list<string>
     */
    public function headers(): array;

    /**
     * Return the rows to export.
     *
     * @param  User|null       $user     Null = all users (admin scope).
     * @param  array<string, mixed>  $filters  Dynamic filters (date range, status, user_id, …).
     * @return list<list<scalar>>
     */
    public function rows(?User $user, array $filters = []): array;

    /**
     * Human-readable label for notifications and API responses.
     */
    public function label(): string;

    /**
     * Whether this export requires admin privileges to request.
     * User-scoped exports return false; cross-user exports return true.
     */
    public function isAdminOnly(): bool;
}
