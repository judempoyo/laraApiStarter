<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\ExportableInterface;
use App\Exports\Concerns\AppliesExportFilters;
use App\Models\ApiKey;
use App\Models\User;

class ApiKeysExport implements ExportableInterface
{
    use AppliesExportFilters;

    public function headers(): array
    {
        return ['ID', 'User ID', 'User Email', 'Name', 'Abilities', 'Last Used At', 'Expires At', 'Created At'];
    }

    public function rows(?User $user, array $filters = []): array
    {
        $query = ApiKey::query()->with('user');

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply generic ID / date filters.
        $query = $this->applyFilters($query, $filters);

        if (isset($filters['expired']) && $filters['expired']) {
            $query->where('expires_at', '<', now());
        }

        return $query->orderBy('id')->get()
            ->map(fn (ApiKey $k) => [
                $k->id,
                $k->user_id,
                $k->user?->email,
                $k->name,
                implode(', ', $k->abilities ?? ['*']),
                $k->last_used_at?->toIso8601String(),
                $k->expires_at?->toIso8601String(),
                $k->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function label(): string
    {
        return 'API Keys';
    }

    public function isAdminOnly(): bool
    {
        return true;
    }
}
