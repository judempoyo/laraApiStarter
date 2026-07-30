<?php

declare(strict_types=1);

namespace App\Imports;

use App\Contracts\ImportableInterface;
use App\Models\User;

class UserPreferenceImport implements ImportableInterface
{
    public function requiredHeaders(): array
    {
        return ['key', 'value'];
    }

    public function rules(array $row, int $rowIndex): array
    {
        return [
            'key'   => ['required', 'string', 'max:255'],
            'value' => ['required'], // value must be present, can be a string or JSON string
        ];
    }

    public function import(array $row, ?User $user): void
    {
        if (! $user) {
            throw new \InvalidArgumentException('A user model is required for user-scoped imports.');
        }

        // Try to decode value if it's a JSON string. If not, keep as is.
        $value = $row['value'];
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        $user->preferences()->updateOrCreate(
            ['key' => $row['key']],
            ['value' => $value]
        );
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
