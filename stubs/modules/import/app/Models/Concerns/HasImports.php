<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Import;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasImports
{
    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public function initializeHasImports(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
