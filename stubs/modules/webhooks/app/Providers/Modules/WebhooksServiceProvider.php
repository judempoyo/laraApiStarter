<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use App\Enums\WebhookEvent;
use App\Events\IntegrationEvent;
use App\Services\WebhookDispatcher;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class WebhooksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen('App\\Events\\*', function (string $name, array $payload): void {
            $event = $payload[0] ?? null;
            if ($event instanceof IntegrationEvent) {
                $type = WebhookEvent::tryFrom($event->name());
                if ($type !== null) {
                    WebhookDispatcher::dispatch($type, $event->payload, $event->userId);
                }
            }
        });
    }
}
