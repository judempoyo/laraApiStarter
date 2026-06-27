<?php

declare(strict_types=1);

namespace App\Enums\Result\Auth;

enum UpdatePasswordResult: string {
    case SUCCESS = 'SUCCESS';
    case INVALID_CURRENT_PASSWORD = 'INVALID_CURRENT_PASSWORD';
}
