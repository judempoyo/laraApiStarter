<?php

declare(strict_types=1);

namespace App\Enums;


use App\Enums\Result\Auth\LoginResult;
use App\Enums\Result\Auth\PasswordResetResult;
use App\Enums\Result\Auth\UpdateEmailResult;
use App\Enums\Result\Auth\UpdatePasswordResult;
use App\Enums\Result\Auth\UpdatePaymentStatusResult;
use App\Enums\Result\Auth\UpdateProfileResult;

enum PublicEnum: string {

    case ERROR_CODE           = 'error-code';
    case USER_ROLE         = 'user-role';
    case USER_STATUS         = 'user-status';
    case SECURITY_EVENT = 'security-event';


    case RESULT_LOGIN                   = 'result-login';
    case RESULT_PASSWORD_RESET          = 'result-password-reset';
    case RESULT_UPDATE_EMAIL            = 'result-update-email';
    case RESULT_UPDATE_PASSWORD         = 'result-update-password';
    case RESULT_UPDATE_PAYMENT_STATUS   = 'result-update-payment-status';
    case RESULT_UPDATE_PROFILE          = 'result-update-profile';

    public const VALUES = [
        self::ERROR_CODE->value,
        self::USER_ROLE->value,
        self::USER_STATUS->value,
        self::SECURITY_EVENT->value,
        self::RESULT_LOGIN->value,
        self::RESULT_PASSWORD_RESET->value,
        self::RESULT_UPDATE_EMAIL->value,
        self::RESULT_UPDATE_PASSWORD->value,
        self::RESULT_UPDATE_PAYMENT_STATUS->value,
        self::RESULT_UPDATE_PROFILE->value,
    ];

    public function class (): string
    {
        return match ($this) {
            self::ERROR_CODE                    => ErrorCode::class,
            self::USER_ROLE                     => UserRole::class,
            self::USER_STATUS                     => UserStatus::class,
            self::SECURITY_EVENT => SecurityEvent::class,
            self::RESULT_LOGIN                   => LoginResult::class,
            self::RESULT_PASSWORD_RESET          => PasswordResetResult::class,
            self::RESULT_UPDATE_EMAIL            => UpdateEmailResult::class,
            self::RESULT_UPDATE_PASSWORD         => UpdatePasswordResult::class,
            self::RESULT_UPDATE_PAYMENT_STATUS   => UpdatePaymentStatusResult::class,
            self::RESULT_UPDATE_PROFILE          => UpdateProfileResult::class,
        };
    }
}
