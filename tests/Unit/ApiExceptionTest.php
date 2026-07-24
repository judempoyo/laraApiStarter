<?php

declare(strict_types=1);

use App\Exceptions\ApiException;
use App\Enums\ErrorCode;

// ─── Factory methods ──────────────────────────────────────────────────────────

it('unauthorized factory creates a 401 with INVALID_CREDENTIALS code', function (): void {
    $e = ApiException::unauthorized();

    expect($e->getStatusCode())->toBe(401);
    expect($e->errorCode)->toBe(ErrorCode::INVALID_CREDENTIALS);
});

it('forbidden factory creates a 403', function (): void {
    $e = ApiException::forbidden();

    expect($e->getStatusCode())->toBe(403);
    expect($e->errorCode)->toBe(ErrorCode::FORBIDDEN);
});

it('notFound factory creates a 404 with RESOURCE_NOT_FOUND code', function (): void {
    $e = ApiException::notFound('User');

    expect($e->getStatusCode())->toBe(404);
    expect($e->errorCode)->toBe(ErrorCode::RESOURCE_NOT_FOUND);
    expect($e->getMessage())->toContain('User');
});

it('conflict factory creates a 409', function (): void {
    $e = ApiException::conflict('Already exists.');

    expect($e->getStatusCode())->toBe(409);
    expect($e->errorCode)->toBe(ErrorCode::CONFLICT);
});

it('gone factory creates a 410', function (): void {
    $e = ApiException::gone('Resource');

    expect($e->getStatusCode())->toBe(410);
    expect($e->errorCode)->toBe(ErrorCode::GONE);
});

it('validation factory creates a 422', function (): void {
    $e = ApiException::validation('Invalid input.', ['email' => 'Required.']);

    expect($e->getStatusCode())->toBe(422);
    expect($e->errorCode)->toBe(ErrorCode::VALIDATION_FAILED);
    expect($e->errors)->toHaveKey('email');
});

it('tooManyRequests factory creates a 429', function (): void {
    $e = ApiException::tooManyRequests();

    expect($e->getStatusCode())->toBe(429);
    expect($e->errorCode)->toBe(ErrorCode::TOO_MANY_REQUESTS);
});

it('serverError factory creates a 500', function (): void {
    $e = ApiException::serverError('Something broke.');

    expect($e->getStatusCode())->toBe(500);
    expect($e->errorCode)->toBe(ErrorCode::SERVER_ERROR);
});

it('serviceUnavailable factory creates a 503', function (): void {
    $e = ApiException::serviceUnavailable();

    expect($e->getStatusCode())->toBe(503);
    expect($e->errorCode)->toBe(ErrorCode::SERVICE_UNAVAILABLE);
});

it('exception carries userMessage separately from technical message', function (): void {
    $e = ApiException::notFound('Post');

    expect($e->getMessage())->toContain('Post not found');
    expect($e->userMessage)->toContain('does not exist');
});
