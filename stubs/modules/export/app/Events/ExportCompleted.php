<?php

declare(strict_types=1);

namespace App\Events;

class ExportCompleted extends IntegrationEvent
{
    public function name(): string
    {
        return 'export.completed';
    }
}
