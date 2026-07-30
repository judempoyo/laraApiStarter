<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\WebhookEvent;
use App\Enums\WebhookStatus;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebhookDeliveryJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum delivery attempts before marking as permanently failed.
     */
    public int $tries = 3;

    /**
     * Backoff in seconds between retries (exponential: 60s, 300s, 900s).
     *
     * @var array<int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly Webhook $webhook,
        private readonly WebhookEvent $event,
        private readonly array $payload,
    ) {}

    /**
     * Prevent overlapping deliveries for the same webhook.
     *
     * @return array<WithoutOverlapping>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping("webhook:{$this->webhook->id}")];
    }

    public function handle(): void
    {
        $body      = $this->buildPayload();
        $signature = $this->sign($body);

        /** @var WebhookDelivery $delivery */
        $delivery = WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event'      => $this->event->value,
            'payload'    => $this->payload,
            'status'     => WebhookStatus::PENDING,
            'attempt'    => $this->attempts(),
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'  => 'application/json',
                    'X-Signature'   => 'sha256=' . $signature,
                    'X-Event'       => $this->event->value,
                    'X-Delivery-Id' => (string) $delivery->id,
                ])
                ->post($this->webhook->url, $body);

            $delivery->update([
                'status'        => $response->successful() ? WebhookStatus::SUCCESS : WebhookStatus::FAILED,
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'delivered_at'  => now(),
            ]);

        } catch (ConnectionException | Throwable $e) {
            $delivery->update([
                'status'       => WebhookStatus::FAILED,
                'response_body' => substr($e->getMessage(), 0, 1000),
            ]);

            // Re-throw so Laravel can retry via $backoff.
            throw $e;
        }
    }

    /**
     * Build the JSON-encodable payload body.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(): array
    {
        return [
            'event'     => $this->event->value,
            'timestamp' => now()->toIso8601String(),
            'data'      => $this->payload,
        ];
    }

    /**
     * Sign the payload with HMAC-SHA256 using the webhook secret.
     */
    private function sign(array $body): string
    {
        return hash_hmac('sha256', json_encode($body), $this->webhook->secret);
    }
}
