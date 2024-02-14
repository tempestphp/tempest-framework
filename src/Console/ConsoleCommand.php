<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use ReflectionMethod;

#[Attribute]
final class ConsoleCommand
{
    public ReflectionMethod $handler;

    public function __construct(
        private readonly ?string $name = null,
        private readonly ?string $description = null,
    ) {}

    public function setHandler(ReflectionMethod $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->handler->getName() === '__invoke'
            ? strtolower($this->handler->getDeclaringClass()->getShortName())
            : strtolower($this->handler->getDeclaringClass()->getShortName() . ':' . $this->handler->getName());
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
