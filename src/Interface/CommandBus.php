<?php

declare(strict_types=1);

namespace Tempest\Interface;

use ReflectionMethod;

interface CommandBus
{
    public function dispatch(object $command): void;

    public function addHandler(string $commandName, ReflectionMethod $handler): self;

    public function addMiddleware(string $middleware): self;
}
