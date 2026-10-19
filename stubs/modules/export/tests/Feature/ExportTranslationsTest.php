<?php

declare(strict_types=1);

it('export has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/export.php');
    $fr = require lang_path('fr/modules/export.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/export.'.$key))->not->toBe('modules/export.'.$key);
        }
    }
});
