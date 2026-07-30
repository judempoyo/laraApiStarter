<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WebhookEvent;
use App\Jobs\WebhookDeliveryJob;
use App\Models\Webhook;

class WebhookDispatcher
{
    /**
     * Dispatch a webhook event to all active webhooks subscribed to it.
     *
     * This method is the single entry point for triggering webhook deliveries.
     * It queries active webhooks for the given user/globally and dispatches
     * a WebhookDeliveryJob for each subscriber.
     *
     * @param  WebhookEvent  $event    The event that occurred.
     * @param  array         $payload  Data describing the event (will be JSON-encoded).
     * @param  int|null      $userId   Scope to a specific user's webhooks (null = all users).
     */
    public static function dispatch(WebhookEvent $event, array $payload, ?int $userId = null): void
    {
        $query = Webhook::query()
            ->where('is_active', true)
            ->whereJsonContains('events', $event->value);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $webhooks = $query->get();

        foreach ($webhooks as $webhook) {
            WebhookDeliveryJob::dispatch($webhook, $event, $payload);
        }
    }
}
