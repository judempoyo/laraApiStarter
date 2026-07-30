<?php

declare(strict_types=1);

namespace App\Actions\Webhook;

use App\DTOs\Webhook\CreateWebhookDTO;
use App\Models\Webhook;
use Illuminate\Support\Str;

class CreateWebhookAction
{
    public function execute(CreateWebhookDTO $dto): Webhook
    {
        return Webhook::create([
            'user_id'     => $dto->userId,
            'url'         => $dto->url,
            'events'      => $dto->events,
            'secret'      => Str::random(40), // Auto-generated signing secret
            'is_active'   => true,
            'description' => $dto->description,
        ]);
    }
}
