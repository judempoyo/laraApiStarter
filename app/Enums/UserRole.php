<?php

namespace App\Enums;

enum UserRole : string
{
    case ADMIN = 'admin';
    case USER = 'user';

    public static function businessRoles(): array
    {
        return [
            self::USER->value,
            self::ADMIN->value,
        ];
    }
}
