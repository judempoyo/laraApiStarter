<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\ExportableInterface;
use App\Exports\Concerns\AppliesExportFilters;
use App\Models\User;

/**
 * Admin-only export — exports one, a subset or all users.
 *
 * Supported filters:
 *   - ids        (array<int>)  Specific user IDs to export.
 *   - id_from    (int)         Minimum user ID.
 *   - id_to      (int)         Maximum user ID.
 *   - status     (string)      Filter by UserStatus value.
 *   - role       (string)      Filter by role name.
 *   - date_from  (string)      Registration date lower bound (ISO 8601).
 *   - date_to    (string)      Registration date upper bound (ISO 8601).
 *
 * Examples:
 *   Export user #42 only         → filters: { user_id: 42 }
 *   Export users with IDs 1–100  → filters: { id_from: 1, id_to: 100 }
 *   Export users #5, #10, #15    → filters: { ids: [5, 10, 15] }
 *   Export all admins            → filters: { role: "admin" }
 */
class UsersExport implements ExportableInterface
{
    use AppliesExportFilters;

    public function headers(): array
    {
        return ['ID', 'Name', 'Email', 'Status', 'Roles', 'Email Verified At', 'Created At'];
    }

    public function rows(?User $user, array $filters = []): array
    {
        $query = User::query()->with('roles');

        // If a single user is passed, scope directly to it.
        if ($user) {
            $query->where('id', $user->id);
        } else {
            // Apply generic ID / date filters.
            $query = $this->applyFilters($query, $filters);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['role'])) {
                $query->role($filters['role']);
            }
        }

        return $query->orderBy('id')->get()
            ->map(fn (User $u) => [
                $u->id,
                $u->name,
                $u->email,
                $u->status?->value ?? 'active',
                $u->roles->pluck('name')->implode(', '),
                $u->email_verified_at?->toIso8601String(),
                $u->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function label(): string
    {
        return 'Users';
    }

    public function isAdminOnly(): bool
    {
        return true;
    }
}
