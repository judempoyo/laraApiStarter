<?php

declare(strict_types=1);

it('impersonation has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/impersonation.php');
    $fr = require lang_path('fr/modules/impersonation.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/impersonation.'.$key))->not->toBe('modules/impersonation.'.$key);
        }
    }
});
