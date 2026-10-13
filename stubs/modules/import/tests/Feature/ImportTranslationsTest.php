<?php

declare(strict_types=1);

it('import has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/import.php');
    $fr = require lang_path('fr/modules/import.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/import.'.$key))->not->toBe('modules/import.'.$key);
        }
    }
});
