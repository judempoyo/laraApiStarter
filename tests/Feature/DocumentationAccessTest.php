<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;

it('denies documentation with no configured key outside local', function (): void {
    config(['app.docs_access_key' => null]);
    expect(Gate::allows('viewApiDocs'))->toBeFalse();
    $this->get('/docs/api')->assertForbidden();
    $this->get('/docs/api.json')->assertForbidden();
});

it('accepts only the configured nonempty documentation key', function (): void {
    config(['app.docs_access_key' => 'configured-test-secret']);
    request()->cookies->set('docs_access_key', 'wrong-key');
    expect(Gate::allows('viewApiDocs'))->toBeFalse();
    request()->cookies->set('docs_access_key', 'configured-test-secret');
    expect(Gate::allows('viewApiDocs'))->toBeTrue();
});

it('serves Scalar with a nonce when the configured key is presented', function (): void {
    config(['app.docs_access_key' => 'configured-test-secret']);
    $response = $this->withUnencryptedCookies(['docs_access_key' => 'configured-test-secret'])
        ->get('/docs/api')
        ->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/html');
    expect($response->headers->get('Content-Security-Policy'))->toContain("'nonce-");
    expect($response->getContent())->toContain('nonce="');
});
