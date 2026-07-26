<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

// ─── GET /v1/health ───────────────────────────────────────────────────────────

it('health endpoint returns 200 when all services are operational', function (): void {
    $response = $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'status',
                'timestamp',
                'version',
                'environment',
                'services' => [
                    'database' => ['status'],
                    'cache'    => ['status'],
                    'queue'    => ['status'],
                    'storage'  => ['status'],
                ],
            ],
        ]);

    expect($response->json('data.status'))->toBe('healthy');
    expect($response->json('data.services.database.status'))->toBe('up');
    expect($response->json('data.services.cache.status'))->toBe('up');
});

it('health endpoint is publicly accessible without authentication', function (): void {
    $this->getJson('/api/v1/health')->assertOk();
});

// ─── Security Middleware ──────────────────────────────────────────────────────

it('requests with suspicious patterns are rejected with 400', function (): void {
    // Path traversal
    $this->getJson('/api/v1/health?path=../etc/passwd')
        ->assertStatus(400);
});

it('request id header is present on every response', function (): void {
    $response = $this->getJson('/api/v1/health');

    expect($response->headers->has('X-Request-Id'))->toBeTrue();
});

it('security headers are present on every response', function (): void {
    $response = $this->getJson('/api/v1/health');

    expect($response->headers->has('X-Content-Type-Options'))->toBeTrue();
    expect($response->headers->has('X-Frame-Options'))->toBeTrue();
    expect($response->headers->has('Strict-Transport-Security'))->toBeTrue();
});

// ─── ApiResponse format ───────────────────────────────────────────────────────

it('unauthenticated request to protected route returns standard 401 error format', function (): void {
    $this->getJson('/api/v1/auth/user')
        ->assertUnauthorized()
        ->assertJsonStructure([
            'code',
            'success',
            'error' => ['code', 'message'],
        ])
        ->assertJsonFragment(['success' => false]);
});

it('route not found returns standard 404 error format', function (): void {
    $this->getJson('/api/v1/non-existent-route-xyz')
        ->assertNotFound()
        ->assertJsonStructure([
            'code',
            'success',
            'error' => ['code', 'message'],
        ]);
});

it('method not allowed returns standard 405 error format', function (): void {
    // health only accepts GET
    $this->postJson('/api/v1/health')
        ->assertStatus(405)
        ->assertJsonFragment(['success' => false]);
});
