<?php

declare(strict_types=1);

namespace App\DTOs\Media;

readonly class UploadMediaDTO
{
    public function __construct(
        public string $collection,
        public ?string $disk = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            collection: $validated['collection'] ?? 'documents',
            disk: $validated['disk'] ?? null,
        );
    }
}
