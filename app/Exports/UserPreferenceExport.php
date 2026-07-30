<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\ExportableInterface;
use App\Exports\Concerns\AppliesExportFilters;
use App\Models\User;

class UserPreferenceExport implements ExportableInterface
{
    use AppliesExportFilters;

    public function headers(): array
    {
        return ['ID', 'Key', 'Value', 'Created At', 'Updated At'];
    }

    public function rows(?User $user, array $filters = []): array
    {
        $query = $user
            ? $user->preferences()->getQuery()
            : \App\Models\UserPreference::query();

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('key')->get()
            ->map(fn ($pref) => [
                $pref->id,
                $pref->key,
                is_array($pref->value) ? json_encode($pref->value) : $pref->value,
                $pref->created_at?->toIso8601String(),
                $pref->updated_at?->toIso8601String(),
            ])
            ->all();
    }

    public function label(): string
    {
        return 'User Preferences';
    }

    public function isAdminOnly(): bool
    {
        return false;
    }
}
