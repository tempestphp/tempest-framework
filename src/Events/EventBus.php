<?php

declare(strict_types=1);

namespace Tempest\Events;

use Tempest\Container\InitializedBy;

#[InitializedBy(EventBusInitializer::class)]
interface EventBus
{
    public function dispatch(object $event): void;
}
