<?php
namespace App\Enums;

enum SecurityEvent: string {
    case LOGIN_SUCCESS = 'LOGIN_SUCCESS';
    case LOGIN_FAILED = 'LOGIN_FAILED';
    case LOGOUT              = 'LOGOUT';
    case TOKEN_REVOKED       = 'TOKEN_REVOKED';
    case FORCE_LOGOUT_DEVICE = 'FORCE_LOGOUT_DEVICE';
    case PASSWORD_RESET_FORCED = 'PASSWORD_RESET_FORCED';
    case SUSPICIOUS_LOGIN = 'SUSPICIOUS_LOGIN';
    case DEVICE_CHANGED = 'DEVICE_CHANGED';
    case IP_CHANGED = 'IP_CHANGED';
}
