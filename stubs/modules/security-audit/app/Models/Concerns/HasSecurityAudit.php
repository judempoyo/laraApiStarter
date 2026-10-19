<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\UserSecurityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSecurityAudit
{
    public function securityLogs(): HasMany
    {
        return $this->hasMany(UserSecurityLog::class);
    }

    public function initializeHasSecurityAudit(): void
    {
        $this->mergeFillable([]);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
