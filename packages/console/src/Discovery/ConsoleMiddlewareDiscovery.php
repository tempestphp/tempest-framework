<?php

namespace Tempest\Console\Discovery;

use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ConsoleMiddlewareDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ConsoleConfig $consoleConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(ConsoleMiddleware::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        $this->consoleConfig->middleware->add(...$this->discoveryItems);
    }
}
