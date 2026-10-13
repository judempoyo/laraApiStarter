<?php

declare(strict_types=1);

namespace App\DTOs\Webhook;

readonly class CreateWebhookDTO
{
    public function __construct(
        public int $userId,
        public string $url,
        public array $events,
        public string $secret = '',
        public ?string $description = null,
    ) {}

    public static function fromRequest(array $validated, int $userId, string $secret = ''): self
    {
        return new self(
            userId: $userId,
            url: $validated['url'],
            events: $validated['events'],
            secret: $secret,
            description: $validated['description'] ?? null,
        );
    }
}
