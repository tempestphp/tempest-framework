<?php

declare(strict_types=1);

namespace Tempest\EventBus;

use Attribute;
use ReflectionMethod;
use Tempest\Support\Reflection\MethodReflector;

#[Attribute]
final class EventHandler
{
    public string $eventName;

    public MethodReflector $handler;

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
        $this->handler = new MethodReflector(new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        ));
    }
}
