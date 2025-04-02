<?php

namespace Tempest\EventBus;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class EventBusMiddlewareDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly EventBusConfig $eventBusConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(EventBusMiddleware::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        $this->eventBusConfig->middleware->add(...$this->discoveryItems);
    }
}
