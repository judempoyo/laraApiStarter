<?php

declare(strict_types=1);

namespace App\Events;

class ApiKeyCreated extends IntegrationEvent
{
    public function name(): string
    {
        return 'api_key.created';
    }
}
