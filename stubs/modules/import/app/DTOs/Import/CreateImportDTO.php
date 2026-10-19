<?php

declare(strict_types=1);

namespace App\DTOs\Import;

readonly class CreateImportDTO
{
    public function __construct(
        public int $userId,
        public string $resource,
        public bool $dryRun = false,
    ) {}

    public static function fromRequest(array $validated, int $userId): self
    {
        return new self(
            userId: $userId,
            resource: $validated['resource'],
            dryRun: filter_var($validated['dry_run'] ?? false, FILTER_VALIDATE_BOOLEAN),
        );
    }
}
