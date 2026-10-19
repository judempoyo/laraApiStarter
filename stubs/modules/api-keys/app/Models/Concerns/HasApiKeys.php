<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasApiKeys
{
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function initializeHasApiKeys(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
