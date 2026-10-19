<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

abstract class IntegrationEvent
{
    use Dispatchable;

    public function __construct(public array $payload, public ?int $userId = null) {}

    abstract public function name(): string;
}
