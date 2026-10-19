<?php

declare(strict_types=1);

namespace App\Actions\Auth\TwoFactor;

use Illuminate\Support\Str;

class GenerateTwoFactorRecoveryCodesAction
{
    public function execute(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(5));
            $part2 = strtoupper(Str::random(5));
            $codes[] = "{$part1}-{$part2}";
        }

        return $codes;
    }
}
