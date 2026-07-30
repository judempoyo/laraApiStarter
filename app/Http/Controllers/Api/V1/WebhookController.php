<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Webhook\CreateWebhookAction;
use App\Actions\Webhook\DeleteWebhookAction;
use App\Actions\Webhook\UpdateWebhookAction;
use App\DTOs\Webhook\CreateWebhookDTO;
use App\DTOs\Webhook\UpdateWebhookDTO;
use App\Enums\WebhookEvent;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\StoreWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Http\Resources\WebhookDeliveryResource;
use App\Http\Resources\WebhookResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\WebhookDeliveryJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * List all webhooks for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = $request->user()
            ->webhooks()
            ->latest()
            ->get();

        return ApiResponse::success(
            WebhookResource::collection($webhooks),
            __('api.webhooks_retrieved')
        );
    }

    /**
     * Register a new webhook endpoint.
     */
    public function store(StoreWebhookRequest $request, CreateWebhookAction $action): JsonResponse
    {
        $webhook = $action->execute(
            CreateWebhookDTO::fromRequest($request->validated(), $request->user()->id)
        );

        return ApiResponse::created(
            new WebhookResource($webhook),
            __('api.webhook_created')
        );
    }

    /**
     * Update an existing webhook.
     */
    public function update(UpdateWebhookRequest $request, int $id, UpdateWebhookAction $action): JsonResponse
    {
        $webhook = $request->user()->webhooks()->find($id);

        if (! $webhook) {
            throw ApiException::notFound('Webhook');
        }

        $webhook = $action->execute(
            $webhook,
            UpdateWebhookDTO::fromRequest($request->validated())
        );

        return ApiResponse::success(
            new WebhookResource($webhook),
            __('api.webhook_updated')
        );
    }

    /**
     * Delete a webhook (cascades to its deliveries).
     */
    public function destroy(Request $request, int $id, DeleteWebhookAction $action): JsonResponse
    {
        $webhook = $request->user()->webhooks()->find($id);

        if (! $webhook) {
            throw ApiException::notFound('Webhook');
        }

        $action->execute($webhook);

        return ApiResponse::noContent(__('api.webhook_deleted'));
    }

    /**
     * List delivery history for a webhook.
     */
    public function deliveries(Request $request, int $id): JsonResponse
    {
        $webhook = $request->user()->webhooks()->find($id);

        if (! $webhook) {
            throw ApiException::notFound('Webhook');
        }

        $deliveries = $webhook->deliveries()
            ->latest()
            ->paginate(20);

        return ApiResponse::paginated($deliveries, __('api.webhook_deliveries'));
    }

    /**
     * Retry a specific failed delivery.
     */
    public function redeliver(Request $request, int $webhookId, int $deliveryId): JsonResponse
    {
        $webhook  = $request->user()->webhooks()->find($webhookId);

        if (! $webhook) {
            throw ApiException::notFound('Webhook');
        }

        $delivery = $webhook->deliveries()->find($deliveryId);

        if (! $delivery) {
            throw ApiException::notFound('Webhook delivery');
        }

        WebhookDeliveryJob::dispatch(
            $webhook,
            WebhookEvent::from($delivery->event),
            $delivery->payload
        );

        return ApiResponse::accepted(__('api.webhook_redelivered'));
    }

    /**
     * List all available webhook events.
     */
    public function events(): JsonResponse
    {
        $events = collect(WebhookEvent::cases())->map(fn ($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        return ApiResponse::success($events, __('api.available_events'));
    }
}
