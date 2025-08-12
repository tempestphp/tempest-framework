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
        foreach ($class->getPublicMethods() as $method) {
            $policy = $method->getAttribute(PolicyFor::class);

            if (! $policy) {
                continue;
            }

            $this->discoveryItems->add($location, [$method, $policy]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $policy]) {
            $this->authConfig->registerPolicy($method, $policy);
        }
    }
}
