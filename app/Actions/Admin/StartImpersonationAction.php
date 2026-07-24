<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Actions\Security\LogSecurityEventAction;
use App\Contracts\Auth\TokenServiceInterface;
use App\Enums\SecurityEvent;
use App\Exceptions\ApiException;
use App\Models\User;

class StartImpersonationAction
{
    public function __construct(
        private readonly TokenServiceInterface $tokenService,
        private readonly LogSecurityEventAction $logSecurityEvent,
    ) {}

    /**
     * Create a short-lived token in the name of the target user.
     * The admin's identity is logged for audit purposes.
     */
    public function execute(User $admin, User $target): array
    {
        if ($admin->id === $target->id) {
            throw ApiException::unprocessable(
                'You cannot impersonate yourself.',
                \App\Enums\ErrorCode::IMPERSONATION_SELF
            );
        }

        $token = $this->tokenService->createToken(
            $target,
            "impersonated_by_{$admin->id}",
            ['*']
        );

        $this->logSecurityEvent->execute(
            $admin,
            SecurityEvent::IMPERSONATION_STARTED->value,
            ['target_user_id' => $target->id, 'target_email' => $target->email]
        );

        return [
            'user'       => $target,
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_at' => $this->tokenService->getTokenExpiry(),
        ];
    }
}
