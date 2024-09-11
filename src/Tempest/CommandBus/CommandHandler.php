<?php

declare(strict_types=1);

namespace Tempest\CommandBus;

use Attribute;
use Tempest\Reflection\MethodReflector;

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
}
