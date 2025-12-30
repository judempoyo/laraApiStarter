<?php

namespace App\DTOs\Auth;

class UpdatePasswordDTO
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            currentPassword: $validated['current_password'],
            newPassword: $validated['password'],
        );
    }
}
