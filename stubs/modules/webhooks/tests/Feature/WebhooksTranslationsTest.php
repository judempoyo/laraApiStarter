<?php

declare(strict_types=1);

it('webhooks has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/webhooks.php');
    $fr = require lang_path('fr/modules/webhooks.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/webhooks.'.$key))->not->toBe('modules/webhooks.'.$key);
        }
    }
});
