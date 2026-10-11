<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPreferences
{
    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    public function initializeHasPreferences(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
