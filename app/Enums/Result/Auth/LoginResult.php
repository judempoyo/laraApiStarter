<?php

declare(strict_types=1);

namespace App\Enums\Result\Auth;

enum LoginResult: string {
    case SUCCESS             = 'SUCCESS';
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
    case USER_DISABLED       = 'USER_DISABLED';
}
