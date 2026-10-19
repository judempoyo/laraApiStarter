<?php

declare(strict_types=1);

it('preferences has matching en and fr translation keys', function (): void {
    $en = require lang_path('en/modules/preferences.php');
    $fr = require lang_path('fr/modules/preferences.php');
    expect(array_keys($en))->toBe(array_keys($fr));
    foreach (array_keys($en) as $key) {
        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            expect(__('modules/preferences.'.$key))->not->toBe('modules/preferences.'.$key);
        }
    }
});
