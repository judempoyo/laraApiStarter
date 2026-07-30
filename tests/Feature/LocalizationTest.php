<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    // ── SetLocale middleware ───────────────────────────────────────────────

    public function test_api_returns_english_by_default(): void
    {
        // No Accept-Language header → falls back to en
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrong',
        ]);

        // The important thing is the response is in english
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_api_respects_french_accept_language_header(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['Accept-Language' => 'fr'])
            ->actingAs($user, config('api.auth_guard', 'sanctum'))
            ->getJson('/api/v1/auth/user');

        $response->assertOk();
        $this->assertEquals('fr', app()->getLocale());
    }

    public function test_api_handles_quality_value_accept_language(): void
    {
        // "fr-FR,fr;q=0.9,en;q=0.8" should resolve to 'fr'
        $user = User::factory()->create();

        $this->withHeaders(['Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8'])
            ->actingAs($user, config('api.auth_guard', 'sanctum'))
            ->getJson('/api/v1/auth/user');

        $this->assertEquals('fr', app()->getLocale());
    }

    public function test_unsupported_locale_falls_back_to_default(): void
    {
        $user = User::factory()->create();

        $this->withHeaders(['Accept-Language' => 'de'])
            ->actingAs($user, config('api.auth_guard', 'sanctum'))
            ->getJson('/api/v1/auth/user');

        $this->assertEquals('en', app()->getLocale());
    }

    // ── Translation keys exist ────────────────────────────────────────────

    public function test_english_translation_file_has_all_keys(): void
    {
        $keys = [
            'register_success', 'login_success', 'logout_success',
            'token_refreshed', 'email_verified', 'profile_updated',
            'api_key_created', 'api_key_revoked', 'webhook_created',
            'webhook_updated', 'webhook_deleted', 'media_uploaded',
            'media_deleted', 'export_queued', 'export_ready',
        ];

        foreach ($keys as $key) {
            $this->assertNotEquals(
                "api.{$key}",
                __("api.{$key}"),
                "Missing English translation key: api.{$key}"
            );
        }
    }

    public function test_french_translation_file_has_all_keys(): void
    {
        app()->setLocale('fr');

        $keys = [
            'register_success', 'login_success', 'logout_success',
            'token_refreshed', 'email_verified', 'profile_updated',
            'api_key_created', 'api_key_revoked', 'webhook_created',
            'webhook_updated', 'webhook_deleted', 'media_uploaded',
            'media_deleted', 'export_queued', 'export_ready',
        ];

        foreach ($keys as $key) {
            $this->assertNotEquals(
                "api.{$key}",
                __("api.{$key}"),
                "Missing French translation key: api.{$key}"
            );
        }
    }
}
