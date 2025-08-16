<?php

namespace Tempest\Auth;

use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class AuthenticatableDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private AuthConfig $authConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(CanAuthenticate::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $discoveryItem) {
            $this->authConfig->authenticatables[] = $discoveryItem;
        }
    }
}
