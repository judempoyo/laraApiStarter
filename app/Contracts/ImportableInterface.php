<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

/**
 * Contract for any resource that can be imported.
 *
 * Register implementations in config/import.php under 'resources'.
 */
interface ImportableInterface
{
    /**
     * Get the required headers/keys for the import.
     * Used to validate the structure of the CSV or JSON columns.
     *
     * @return list<string>
     */
    public function requiredHeaders(): array;

    /**
     * Return validation rules for a single row.
     *
     * @param  array<string, mixed>  $row
     * @param  int                   $rowIndex  1-indexed row number.
     * @return array<string, mixed>
     */
    public function rules(array $row, int $rowIndex): array;

    /**
     * Process a single row and import it.
     *
     * @param  array<string, mixed>  $row
     * @param  User|null             $user  The user executing the import (null for admin/all).
     * @return void
     */
    public function import(array $row, ?User $user): void;

    /**
     * Human-readable label for notifications and API responses.
     */
    public function label(): string;

    /**
     * Whether this import requires admin privileges to request.
     */
    public function isAdminOnly(): bool;
}
