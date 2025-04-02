<?php

namespace Tempest\CommandBus;

use Tempest\Console\ConsoleConfig;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class CommandBusMiddlewareDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly CommandBusConfig $commandBusConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(CommandBusMiddleware::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        $this->commandBusConfig->middleware->add(...$this->discoveryItems);
    }
}
