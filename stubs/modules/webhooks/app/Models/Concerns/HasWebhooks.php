<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Webhook;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasWebhooks
{
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function initializeHasWebhooks(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
