<?php

namespace Tempest\Interface;

use Tempest\Container\InitializedBy;
use Tempest\Events\EventBusInitializer;

#[InitializedBy(EventBusInitializer::class)]
interface EventBus
{
    public function dispatch(object $event): void;
}