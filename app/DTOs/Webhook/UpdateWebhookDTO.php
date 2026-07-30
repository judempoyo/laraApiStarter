<?php

declare(strict_types=1);

namespace App\DTOs\Webhook;

readonly class UpdateWebhookDTO
{
    public function __construct(
        public ?string $url = null,
        public ?array $events = null,
        public ?bool $isActive = null,
        public ?string $description = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            url: $validated['url'] ?? null,
            events: $validated['events'] ?? null,
            isActive: isset($validated['is_active']) ? (bool) $validated['is_active'] : null,
            description: $validated['description'] ?? null,
        );
    }
}
