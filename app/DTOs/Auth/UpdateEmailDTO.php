<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

class UpdateEmailDTO
{
    public function __construct(
        public readonly string $email,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            email: $validated['email'],
        );
    }
}
