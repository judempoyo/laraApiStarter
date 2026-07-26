<?php

declare (strict_types = 1);

namespace App\Enums\Result\Auth;

enum TwoFactorResult: string {
    case ENABLED          = 'ENABLED';
    case CONFIRMED        = 'CONFIRMED';
    case DISABLED         = 'DISABLED';
    case VERIFIED         = 'VERIFIED';
    case ALREADY_ENABLED  = 'ALREADY_ENABLED';
    case NOT_ENABLED      = 'NOT_ENABLED';
    case NOT_CONFIRMED    = 'NOT_CONFIRMED';
    case INVALID_CODE     = 'INVALID_CODE';
    case INVALID_PASSWORD = 'INVALID_PASSWORD';
}
