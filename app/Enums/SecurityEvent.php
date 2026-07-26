<?php

declare(strict_types=1);

namespace App\Enums;

enum SecurityEvent: string
{
    case LOGIN_SUCCESS          = 'LOGIN_SUCCESS';
    case LOGIN_FAILED           = 'LOGIN_FAILED';
    case LOGOUT                 = 'LOGOUT';
    case TOKEN_REVOKED          = 'TOKEN_REVOKED';
    case FORCE_LOGOUT_DEVICE    = 'FORCE_LOGOUT_DEVICE';
    case PASSWORD_RESET_FORCED  = 'PASSWORD_RESET_FORCED';
    case SUSPICIOUS_LOGIN       = 'SUSPICIOUS_LOGIN';
    case DEVICE_CHANGED         = 'DEVICE_CHANGED';
    case IP_CHANGED             = 'IP_CHANGED';
    case IMPERSONATION_STARTED  = 'IMPERSONATION_STARTED';
    case IMPERSONATION_STOPPED  = 'IMPERSONATION_STOPPED';
    case TWO_FACTOR_ENABLED     = 'TWO_FACTOR_ENABLED';
    case TWO_FACTOR_DISABLED    = 'TWO_FACTOR_DISABLED';
    case API_KEY_CREATED        = 'API_KEY_CREATED';
    case API_KEY_REVOKED        = 'API_KEY_REVOKED';
}
