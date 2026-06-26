<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

class UpdateProfileDTO
{
    public function __construct(
        public readonly string $name,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name: $validated['name'],
        );
    }
}
