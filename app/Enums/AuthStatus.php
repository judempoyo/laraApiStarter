<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthStatus: string
{
    case AUTHENTICATED       = 'AUTHENTICATED';
    case TWO_FACTOR_REQUIRED = 'TWO_FACTOR_REQUIRED';
}
