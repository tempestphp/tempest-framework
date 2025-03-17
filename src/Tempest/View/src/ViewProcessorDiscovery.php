<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ViewProcessorDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ViewConfig $viewConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements(ViewProcessor::class)) {
            return;
        }

        $this->discoveryItems->add($location, [$class->getName()]);
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$className]) {
            $viewProcessor = new ClassReflector($className);

            $this->viewConfig->addViewProcessor($viewProcessor);
        }
    }
}
