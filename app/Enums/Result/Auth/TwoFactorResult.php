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
    case INVALID_CODE               = 'INVALID_CODE';
    case INVALID_PASSWORD           = 'INVALID_PASSWORD';
    case RECOVERY_CODES_REGENERATED = 'RECOVERY_CODES_REGENERATED';
    case RECOVERY_CODES_RETRIEVED   = 'RECOVERY_CODES_RETRIEVED';
    case RESET_SUCCESS              = 'RESET_SUCCESS';
    case RESET_LINK_SENT            = 'RESET_LINK_SENT';
    case INVALID_RECOVERY_CODE      = 'INVALID_RECOVERY_CODE';
}
