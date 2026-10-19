<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;

class UserExportPreparing
{
    use Dispatchable;

    public function __construct(public Builder $query, public array $filters) {}
}
