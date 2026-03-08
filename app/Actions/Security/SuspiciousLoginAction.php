<?php
namespace App\Actions\Security;

use App\Enums\SecurityEvent;
use App\Models\User;

class SuspiciousLoginAction
{
    public function execute(?User $user, string $reason): void
    {
        app(LogSecurityEventAction::class)->execute(
            $user,
            SecurityEvent::SUSPICIOUS_LOGIN->value,
            $reason
        );
    }
}
