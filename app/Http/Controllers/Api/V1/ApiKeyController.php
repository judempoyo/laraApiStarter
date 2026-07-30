<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\WebhookEvent;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiKeyRequest;
use App\Http\Responses\ApiResponse;
use App\Services\WebhookDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * List all API keys for the authenticated user (without the secret).
     */
    public function index(Request $request): JsonResponse
    {
        $keys = $request->user()
            ->apiKeys()
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at']);

        return ApiResponse::success($keys, __('api.api_keys_retrieved'));
    }

    /**
     * Create a new API key. The plain-text key is returned only once.
     */
    public function store(StoreApiKeyRequest $request): JsonResponse
    {
        $plainKey = Str::random(64);
        $hashed   = hash('sha256', $plainKey);

        $apiKey = $request->user()->apiKeys()->create([
            'name'       => $request->validated('name'),
            'key'        => $hashed,
            'abilities'  => $request->validated('abilities', ['*']),
            'expires_at' => $request->filled('expires_in_days')
                ? now()->addDays((int) $request->validated('expires_in_days'))
                : null,
        ]);

        // Fire webhook event asynchronously.
        WebhookDispatcher::dispatch(WebhookEvent::API_KEY_CREATED, [
            'api_key_id'  => $apiKey->id,
            'name'        => $apiKey->name,
            'abilities'   => $apiKey->abilities,
            'expires_at'  => $apiKey->expires_at?->toIso8601String(),
        ], $request->user()->id);

        return ApiResponse::created([
            'id'         => $apiKey->id,
            'name'       => $apiKey->name,
            'key'        => $plainKey,
            'abilities'  => $apiKey->abilities,
            'expires_at' => $apiKey->expires_at?->toIso8601String(),
        ], __('api.api_key_created'));
    }

    /**
     * Revoke an API key.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $apiKey = $request->user()->apiKeys()->find($id);

        if (! $apiKey) {
            throw ApiException::notFound('API key');
        }

        $apiKeyData = [
            'api_key_id' => $apiKey->id,
            'name'       => $apiKey->name,
        ];

        $apiKey->delete();

        // Fire webhook event asynchronously.
        WebhookDispatcher::dispatch(WebhookEvent::API_KEY_DELETED, $apiKeyData, $request->user()->id);

        return ApiResponse::noContent(__('api.api_key_revoked'));
    }
}
