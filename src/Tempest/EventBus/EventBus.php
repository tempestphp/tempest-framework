<?php

declare(strict_types=1);

namespace Tempest\EventBus;

interface EventBus
{
    public function dispatch(string|object $event): void;
}
