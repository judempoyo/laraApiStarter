<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasTwoFactor
{
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function getRecoveryCodes(): array
    {
        if (empty($this->two_factor_recovery_codes)) {
            return [];
        }

        try {
            $decrypted = decrypt($this->two_factor_recovery_codes);

            return is_array($decrypted) ? $decrypted : (json_decode($decrypted, true) ?? []);
        } catch (\Throwable) {
            return [];
        }
    }

    public function replaceRecoveryCodes(array $codes): void
    {
        $this->update([
            'two_factor_recovery_codes' => encrypt($codes),
        ]);
    }

    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->getRecoveryCodes();
        $cleanedCode = strtoupper(trim(str_replace([' ', '-'], '', $code)));

        foreach ($codes as $index => $storedCode) {
            $cleanedStored = strtoupper(trim(str_replace([' ', '-'], '', $storedCode)));
            if (hash_equals($cleanedStored, $cleanedCode)) {
                unset($codes[$index]);
                $this->replaceRecoveryCodes(array_values($codes));

                return true;
            }
        }

        return false;
    }

    public function resetTwoFactor(): void
    {
        $this->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function initializeHasTwoFactor(): void
    {
        $this->mergeFillable(['two_factor_secret', 'two_factor_confirmed_at', 'two_factor_recovery_codes']);
        $this->mergeHidden(['two_factor_secret', 'two_factor_recovery_codes']);
        $this->mergeCasts(['two_factor_confirmed_at' => 'datetime']);
    }
}
