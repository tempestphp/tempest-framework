<?php

declare(strict_types=1);

namespace Tempest\Bus;

use ReflectionMethod;

final class CommandBusConfig
{
    public function __construct(
        /** @var \Tempest\Bus\CommandHandler[] */
        public array $handlers = [],

        /** @var \Tempest\Bus\Middleware[] */
        public array $middleware = [],
    ) {
    }

    public function addHandler(string $commandName, ReflectionMethod $handler): self
    {
        $this->handlers[$commandName] = new CommandHandler($handler);

        return $this;
    }

    public function addMiddleware(string $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }
}
