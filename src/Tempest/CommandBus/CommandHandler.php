<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Attribute;
use ReflectionMethod;
use Tempest\Support\Reflection\MethodReflector;

#[Attribute]
final class CommandHandler
{
    public string $commandName;

    public MethodReflector $handler;

    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

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
            'commandName' => $this->commandName,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->commandName = $data['commandName'];
        $this->handler = new MethodReflector(
            new ReflectionMethod(
                objectOrMethod: $data['handler_class'],
                method: $data['handler_method'],
            ),
        );
    }
}
