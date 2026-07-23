<?php

namespace App\Enums\Auth;

enum LoginStatus: string
{
    case SUCCESS = 'SUCCESS';
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';
}
