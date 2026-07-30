<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Enums\WebhookStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function actingAsUser(): static
    {
        return $this->actingAs($this->user, config('api.auth_guard', 'sanctum'));
    }

    // ── CRUD ─────────────────────────────────────────────────────────────

    public function test_user_can_list_webhooks(): void
    {
        Webhook::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAsUser()->getJson('/api/v1/user/webhooks');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_a_webhook(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/user/webhooks', [
            'url'         => 'https://example.com/hooks',
            'events'      => ['api_key.created'],
            'description' => 'Test webhook',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.url', 'https://example.com/hooks')
            ->assertJsonMissingPath('data.secret'); // Secret must never be exposed

        $this->assertDatabaseHas('webhooks', [
            'user_id' => $this->user->id,
            'url'     => 'https://example.com/hooks',
        ]);
    }

    public function test_webhook_creation_requires_valid_url(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/user/webhooks', [
            'url'    => 'not-a-url',
            'events' => ['api_key.created'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }

    public function test_webhook_creation_requires_valid_events(): void
    {
        $response = $this->actingAsUser()->postJson('/api/v1/user/webhooks', [
            'url'    => 'https://example.com/hooks',
            'events' => ['invalid.event'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['events.0']);
    }

    public function test_user_can_update_a_webhook(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAsUser()->patchJson("/api/v1/user/webhooks/{$webhook->id}", [
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('webhooks', ['id' => $webhook->id, 'is_active' => false]);
    }

    public function test_user_cannot_update_another_users_webhook(): void
    {
        $other   = User::factory()->create();
        $webhook = Webhook::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAsUser()->patchJson("/api/v1/user/webhooks/{$webhook->id}", [
            'is_active' => false,
        ]);

        $response->assertNotFound();
    }

    public function test_user_can_delete_a_webhook(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAsUser()->deleteJson("/api/v1/user/webhooks/{$webhook->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('webhooks', ['id' => $webhook->id]);
    }

    // ── Deliveries ────────────────────────────────────────────────────────

    public function test_user_can_list_webhook_deliveries(): void
    {
        $webhook = Webhook::factory()->create(['user_id' => $this->user->id]);
        WebhookDelivery::factory()->count(5)->create(['webhook_id' => $webhook->id]);

        $response = $this->actingAsUser()->getJson("/api/v1/user/webhooks/{$webhook->id}/deliveries");

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_can_redeliver_a_failed_delivery(): void
    {
        $webhook  = Webhook::factory()->create(['user_id' => $this->user->id]);
        $delivery = WebhookDelivery::factory()->create([
            'webhook_id' => $webhook->id,
            'event'      => 'api_key.created',
            'status'     => WebhookStatus::FAILED,
            'payload'    => ['api_key_id' => 1],
        ]);

        \Illuminate\Support\Facades\Queue::fake();

        $response = $this->actingAsUser()->postJson(
            "/api/v1/user/webhooks/{$webhook->id}/deliveries/{$delivery->id}/redeliver"
        );

        $response->assertStatus(202);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\WebhookDeliveryJob::class);
    }

    // ── Available events ──────────────────────────────────────────────────

    public function test_user_can_list_available_events(): void
    {
        $response = $this->actingAsUser()->getJson('/api/v1/user/webhooks/events');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['value', 'label']]]);
    }

    // ── Unauthenticated ───────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_webhooks(): void
    {
        $this->getJson('/api/v1/user/webhooks')->assertUnauthorized();
    }
}
