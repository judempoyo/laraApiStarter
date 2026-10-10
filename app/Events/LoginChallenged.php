<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class LoginChallenged
{
    use Dispatchable;

    public ?array $result = null;

    public function __construct(public User $user, public string $device) {}
}
