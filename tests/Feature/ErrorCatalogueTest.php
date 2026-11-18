<?php

declare(strict_types=1);

use App\Enums\ErrorCode;

it('renders the error catalogue html page in english by default', function (): void {
    $response = $this->get('/docs/errors');

    $response->assertOk();
    expect($response->getContent())->toContain('API Error Catalogue');
    expect($response->getContent())->toContain('id="UNAUTHENTICATED"');
    expect($response->getContent())->toContain('id="RESOURCE_NOT_FOUND"');
});

it('renders the error catalogue html page in french when requested', function (): void {
    $response = $this->get('/docs/errors?lang=fr');

    $response->assertOk();
    expect($response->getContent())->toContain('Catalogue des erreurs API');
    expect($response->getContent())->toContain('Non authentifié');
});

it('returns json error catalogue when requested', function (): void {
    $response = $this->getJson('/docs/errors');

    $response->assertOk();
    $data = $response->json('data');

    expect($data)->toHaveKey('UNAUTHENTICATED');
    expect($data['UNAUTHENTICATED']['status'])->toBe(401);
});

it('includes all enum error codes exhaustively in the catalogue', function (): void {
    $response = $this->getJson('/docs/errors');
    $data = $response->json('data');

    foreach (ErrorCode::cases() as $case) {
        expect($data)->toHaveKey($case->value);
        expect($data[$case->value]['status'])->toBe($case->httpStatus());
        expect($data[$case->value]['category'])->toBe($case->category());
    }
});

it('redirects root url to docs errors', function (): void {
    $response = $this->get('/');

    $response->assertRedirect('/docs/errors');
});

it('redirects unknown web routes to docs errors to prevent default laravel 404', function (): void {
    $response = $this->get('/non-existent-web-page');

    $response->assertRedirect('/docs/errors');
});
