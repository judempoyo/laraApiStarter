<?php

namespace App\Enums\Result\Auth;
enum PasswordResetResult: string {
    case RESET_SUCCESS = 'RESET_SUCCESS';
    case LINK_SENT    = 'LINK_SENT';
    case INVALID_TOKEN = 'INVALID_TOKEN';
    case INVALID_USER  = 'INVALID_USER';
    case INVALID_PASSWORD = 'INVALID_PASSWORD';
    case THROTTLED     = 'THROTTLED';
}
