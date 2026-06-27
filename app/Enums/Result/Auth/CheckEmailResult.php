<?php

declare(strict_types=1);

namespace App\Enums\Result\Auth;

enum CheckEmailResult: string
{
    case EXISTS = 'EXISTS';
    case NOT_FOUND = 'NOT_FOUND';
}
