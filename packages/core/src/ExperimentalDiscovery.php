<?php

namespace Tempest\Core;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ExperimentalDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly ExperimentalConfig $experimentalConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->hasAttribute(Experimental::class)) {
            $this->discoveryItems->add($location, $class->getAttribute(Experimental::class));
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $experimental) {
            $this->experimentalConfig->experimentalFeatures[] = $experimental;
        }
    }
}
