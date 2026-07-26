<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Security\LogSecurityEventAction;
use App\Contracts\Auth\TokenServiceInterface;
use App\DTOs\Auth\LoginDTO;
use App\Enums\Result\Auth\LoginResult;
use App\Enums\SecurityEvent;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    public function execute(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            app(LogSecurityEventAction::class)->execute(
                $user ?? null,
                SecurityEvent::LOGIN_FAILED->value
            );

            return [
                'status' => LoginResult::INVALID_CREDENTIALS,
            ];
        }

        if ($user->status === UserStatus::DISABLED) {
            return [
                'status' => LoginResult::USER_DISABLED,
            ];
        }

        $user->load(['roles', 'permissions']);

        $lastLogin = $user->securityLogs()
            ->where('event', SecurityEvent::LOGIN_SUCCESS->value)
            ->latest()
            ->first();

        $currentIp     = request()->ip();
        $currentDevice = app(ResolveDeviceNameAction::class)->execute();

        if ($lastLogin) {
            if ($lastLogin->ip_address !== $currentIp) {
                app(LogSecurityEventAction::class)->execute(
                    $user,
                    SecurityEvent::IP_CHANGED->value,
                    [
                        'from' => $lastLogin->ip_address,
                        'to'   => $currentIp,
                    ]
                );
            }

            if (($lastLogin->meta['device'] ?? null) !== $currentDevice) {
                app(LogSecurityEventAction::class)->execute(
                    $user,
                    SecurityEvent::DEVICE_CHANGED->value,
                    [
                        'from' => $lastLogin->meta['device'] ?? null,
                        'to'   => $currentDevice,
                    ]
                );
            }
        }

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $plainToken   = $tokenService->createToken($user, $currentDevice);

        app(LogSecurityEventAction::class)->execute(
            $user,
            SecurityEvent::LOGIN_SUCCESS->value
        );

        return [
            'status'     => LoginResult::SUCCESS,
            'user'       => $user,
            'token'      => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenService->getTokenExpiry(),
        ];
    }
}
