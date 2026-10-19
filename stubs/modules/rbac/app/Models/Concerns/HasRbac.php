<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Permission\Traits\HasRoles;

trait HasRbac
{
    use HasRoles;

    protected $guard_name = 'api';

    public function initializeHasRbac(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
