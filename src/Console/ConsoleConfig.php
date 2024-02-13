<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;

use function Tempest\attribute;

final class ConsoleConfig
{
    public function __construct(
        /** @var \ReflectionMethod[] $handlers */
        public array $handlers = [],
    ) {
    }

    public function addCommand(ReflectionMethod $handler): self
    {
        $attribute = attribute(ConsoleCommand::class)->in($handler)->first();

        $commandName = $attribute->name ?? strtolower($handler->getDeclaringClass()->getShortName() . ':' . $handler->getName());

        $this->handlers[$commandName] = $handler;

        return $this;
    }
}
