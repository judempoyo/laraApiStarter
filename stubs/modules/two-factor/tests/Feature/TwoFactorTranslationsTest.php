<?php

declare(strict_types=1);

it('two-factor has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/two-factor.php');
    $fr = require lang_path('fr/modules/two-factor.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/two-factor.'.$key))->not->toBe('modules/two-factor.'.$key);
        }
    }
});
