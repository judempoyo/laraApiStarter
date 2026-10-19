<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Export;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasExports
{
    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }

    public function initializeHasExports(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
