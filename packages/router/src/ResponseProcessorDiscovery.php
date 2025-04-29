<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ResponseProcessorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly RouteConfig $routeConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(ResponseProcessor::class)) {
            return;
        }

        $this->discoveryItems->add($location, [$class->getName()]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$className]) {
            $viewProcessor = new ClassReflector($className);

            $this->routeConfig->addResponseProcessor($viewProcessor->getName());
        }
    }
}
