<?php

declare(strict_types=1);

namespace App\Actions\Webhook;

use App\Models\Webhook;

class DeleteWebhookAction
{
    public function execute(Webhook $webhook): void
    {
        $webhook->delete();
    }
}
