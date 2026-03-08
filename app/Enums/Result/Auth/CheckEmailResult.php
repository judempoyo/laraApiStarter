<?php

namespace App\Enums\Result\Auth;

enum CheckEmailResult: string
{
    case EXISTS = 'EXISTS';
    case NOT_FOUND = 'NOT_FOUND';
}
