<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Attribute;
use ReflectionMethod;

#[Attribute]
final class CommandHandler
{
    public string $commandName;

    public ReflectionMethod $handler;

    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

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
            'commandName' => $this->commandName,
            'handler_class' => $this->handler->getDeclaringClass()->getName(),
            'handler_method' => $this->handler->getName(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->commandName = $data['commandName'];
        $this->handler = new ReflectionMethod(
            objectOrMethod: $data['handler_class'],
            method: $data['handler_method'],
        );
    }
}
