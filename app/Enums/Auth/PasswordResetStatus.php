<?php

namespace App\Enums\Auth;

enum PasswordResetStatus: string
{
    case LINK_SENT = 'LINK_SENT';
    case RESET_SUCCESS = 'RESET_SUCCESS';
    case INVALID_USER = 'INVALID_USER';
    case INVALID_TOKEN = 'INVALID_TOKEN';
}
