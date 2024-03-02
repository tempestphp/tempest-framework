<?php

declare(strict_types=1);

namespace Tempest\Events;

interface EventBus
{
    public function dispatch(object $event): void;
}
