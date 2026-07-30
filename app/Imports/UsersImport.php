<?php

declare(strict_types=1);

namespace App\Imports;

use App\Contracts\ImportableInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersImport implements ImportableInterface
{
    public function requiredHeaders(): array
    {
        return ['name', 'email', 'password'];
    }

    public function rules(array $row, int $rowIndex): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function import(array $row, ?User $user): void
    {
        User::create([
            'name'              => $row['name'],
            'email'             => $row['email'],
            'password'          => Hash::make($row['password']),
            'email_verified_at' => now(), // Auto-verify imported users
        ]);
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
