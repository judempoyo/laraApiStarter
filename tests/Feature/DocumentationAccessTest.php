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

it('serves documentation with a nonce when the configured key is presented', function (): void {
    config(['app.docs_access_key' => 'configured-test-secret']);

    $scrambleResponse = $this->withUnencryptedCookies(['docs_access_key' => 'configured-test-secret'])
        ->get('/docs/api')
        ->assertOk();
    expect($scrambleResponse->headers->get('Content-Type'))->toContain('text/html');
    expect($scrambleResponse->headers->get('Content-Security-Policy'))->toContain("'nonce-");
    expect($scrambleResponse->getContent())->toContain('nonce="');

    $scalarResponse = $this->withUnencryptedCookies(['docs_access_key' => 'configured-test-secret'])
        ->get('/doc')
        ->assertOk();
    expect($scalarResponse->headers->get('Content-Type'))->toContain('text/html');
    expect($scalarResponse->headers->get('Content-Security-Policy'))->toContain("'nonce-");
    expect($scalarResponse->getContent())->toContain('nonce="');
});
