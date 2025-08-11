<?php

namespace Tempest\Auth\AccessControl;

use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AuthConfig;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class PolicyDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private AuthConfig $authConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Policy::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $discoveryItem) {
            $this->authConfig->policies[] = $discoveryItem;
        }
    }
}
