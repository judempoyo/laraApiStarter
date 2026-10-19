<?php

declare(strict_types=1);

namespace App\Contracts;

interface ActionInterface
{
    /**
     * Execute the action.
     */
    public function execute(mixed ...$args): mixed;
}
