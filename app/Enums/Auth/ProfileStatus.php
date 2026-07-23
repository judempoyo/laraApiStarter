<?php

namespace App\Enums\Auth;

enum ProfileStatus: string
{
    case SUCCESS = 'SUCCESS';
    case INVALID_CURRENT_PASSWORD = 'INVALID_CURRENT_PASSWORD';
}
