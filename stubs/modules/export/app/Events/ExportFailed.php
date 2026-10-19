<?php

declare(strict_types=1);

namespace App\Events;

class ExportFailed extends IntegrationEvent
{
    public function name(): string
    {
        return 'export.failed';
    }
}
