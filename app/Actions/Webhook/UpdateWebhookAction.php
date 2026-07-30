<?php

declare(strict_types=1);

namespace App\Actions\Webhook;

use App\DTOs\Webhook\UpdateWebhookDTO;
use App\Models\Webhook;

class UpdateWebhookAction
{
    public function execute(Webhook $webhook, UpdateWebhookDTO $dto): Webhook
    {
        $webhook->update(array_filter([
            'url'         => $dto->url,
            'events'      => $dto->events,
            'is_active'   => $dto->isActive,
            'description' => $dto->description,
        ], fn ($v) => $v !== null));

        return $webhook->fresh();
    }
}
