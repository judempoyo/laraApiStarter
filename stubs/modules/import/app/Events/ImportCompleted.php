<?php

declare(strict_types=1);

namespace App\Events;

class ImportCompleted extends IntegrationEvent
{
    public function name(): string
    {
        return 'import.completed';
    }
}
