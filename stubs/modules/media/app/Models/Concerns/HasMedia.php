<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

trait HasMedia
{
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->getAttribute('avatar')) {
            return null;
        }

        if (str_starts_with($this->getAttribute('avatar'), 'http')) {
            return $this->getAttribute('avatar');
        }

        return Storage::url($this->getAttribute('avatar'));
    }

    public function initializeHasMedia(): void
    {
        $this->mergeFillable(['avatar']);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
