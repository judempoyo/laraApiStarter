<?php

declare(strict_types=1);

it('api-keys has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/api-keys.php');
    $fr = require lang_path('fr/modules/api-keys.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/api-keys.'.$key))->not->toBe('modules/api-keys.'.$key);
        }
    }
});
