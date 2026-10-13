<?php

declare(strict_types=1);

namespace App\Events;

class ImportFailed extends IntegrationEvent
{
    public function name(): string
    {
        return 'import.failed';
    }
}
