<?php

declare(strict_types=1);

namespace App\DTOs\Export;

use App\Enums\ExportFormat;

readonly class CreateExportDTO
{
    public function __construct(
        public int $userId,
        public string $resource,
        public ExportFormat $format,
        /** @var array<string, mixed> */
        public array $filters = [],
    ) {}

    public static function fromRequest(array $validated, int $userId): self
    {
        return new self(
            userId: $userId,
            resource: $validated['resource'],
            format: ExportFormat::from($validated['format']),
            filters: $validated['filters'] ?? [],
        );
    }
}
