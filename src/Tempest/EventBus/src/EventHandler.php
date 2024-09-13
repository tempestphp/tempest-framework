<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Attribute;
use Tempest\Reflection\MethodReflector;

#[Attribute]
final class EventHandler
{
    public string $eventName;

    public MethodReflector $handler;

    public function __construct(
        public null|string|object $event = null
    ) {
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function setHandler(MethodReflector $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
