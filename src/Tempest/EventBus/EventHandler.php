<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Attribute;
use ReflectionMethod;

#[Attribute]
final class EventHandler
{
    public string $eventName;

    public ReflectionMethod $handler;

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'eventName' => $this->eventName,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->eventName = $data['eventName'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
    }
}
