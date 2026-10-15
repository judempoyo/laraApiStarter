<?php

declare(strict_types=1);

it('security headers are present on every response', function (): void {
    $response = $this->getJson('/api/v1/health');

    expect($response->headers->has('X-Content-Type-Options'))->toBeTrue();
    expect($response->headers->has('X-Frame-Options'))->toBeTrue();
    expect($response->headers->has('Strict-Transport-Security'))->toBeTrue();
});
