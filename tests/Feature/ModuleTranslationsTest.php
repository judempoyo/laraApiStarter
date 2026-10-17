<?php

declare(strict_types=1);

it('ships matching English and French dictionaries for every module', function (): void {
    foreach (glob(base_path('stubs/modules/*/lang/en/modules/*.php')) ?: [] as $path) {
        $english = require $path;
        $frenchPath = str_replace('/lang/en/', '/lang/fr/', $path);
        expect(is_file($frenchPath))->toBeTrue();
        $french = require $frenchPath;
        expect(array_keys($english))->toBe(array_keys($french));
    }
});
