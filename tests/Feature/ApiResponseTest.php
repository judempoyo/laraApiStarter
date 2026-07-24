<?php

declare(strict_types=1);

use App\Http\Responses\ApiResponse;
use App\Enums\ErrorCode;

// ─── ApiResponse::success ─────────────────────────────────────────────────────

it('success response has expected structure', function (): void {
    $response = ApiResponse::success(['foo' => 'bar'], 'OK');
    $body     = $response->getData(true);

    expect($body['success'])->toBeTrue();
    expect($body['code'])->toBe(200);
    expect($body['data']['foo'])->toBe('bar');
    expect($body['message'])->toBe('OK');
    expect($body['error'])->toBeNull();
});

// ─── ApiResponse::created ─────────────────────────────────────────────────────

it('created response returns 201', function (): void {
    $response = ApiResponse::created(['id' => 1], 'Created.');
    $body     = $response->getData(true);

    expect($body['code'])->toBe(201);
    expect($body['success'])->toBeTrue();
    expect($body['data']['id'])->toBe(1);
});

// ─── ApiResponse::accepted ────────────────────────────────────────────────────

it('accepted response returns 202', function (): void {
    $response = ApiResponse::accepted('Queued.');
    $body     = $response->getData(true);

    expect($body['code'])->toBe(202);
    expect($body['success'])->toBeTrue();
});

// ─── ApiResponse::noContent ───────────────────────────────────────────────────

it('noContent response returns 204', function (): void {
    $response = ApiResponse::noContent();

    expect($response->getStatusCode())->toBe(204);
});

// ─── ApiResponse::error ───────────────────────────────────────────────────────

it('error response has expected structure', function (): void {
    $response = ApiResponse::error(ErrorCode::FORBIDDEN, 'Access denied.', 403, 'User message.');
    $body     = $response->getData(true);

    expect($body['success'])->toBeFalse();
    expect($body['code'])->toBe(403);
    expect($body['error']['code'])->toBe('FORBIDDEN');
    expect($body['error']['message'])->toBe('Access denied.');
    expect($body['message'])->toBe('User message.');
    expect($body['data'])->toBeNull();
});
