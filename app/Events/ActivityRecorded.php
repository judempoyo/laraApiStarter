<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ActivityRecorded
{
    use Dispatchable;

    public function __construct(public string $action, public ?string $description = null, public ?int $userId = null) {}
}
