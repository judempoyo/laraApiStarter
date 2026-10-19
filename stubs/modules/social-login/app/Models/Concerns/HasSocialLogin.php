<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasSocialLogin
{
    public function initializeHasSocialLogin(): void
    {
        $this->mergeFillable(['google_id', 'provider', 'provider_id']);
        $this->mergeHidden([]);
        $this->mergeCasts([]);
    }
}
