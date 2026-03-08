<?php
namespace App\Actions\Auth;

use App\Actions\Security\LogSecurityEventAction;
use App\DTOs\Auth\LoginDTO;
use App\Enums\Result\auth\LoginResult;
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
                $user,
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
        $currentDevice = request()->header('Device-Name', 'unknown');

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

        $tokenInstance = $user->createToken($currentDevice);

        app(LogSecurityEventAction::class)->execute(
            $user,
            SecurityEvent::LOGIN_SUCCESS->value
        );

        $expiration = config('sanctum.expiration');

        return [
            'status'     => LoginResult::SUCCESS,
            'user'       => $user,
            'token'      => $tokenInstance->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiration
                ? now()->addMinutes($expiration)->toIso8601String()
                : null,
        ];
    }
}
