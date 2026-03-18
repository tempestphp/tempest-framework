<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class ResettableDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly Container $container,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Resettable::class)) {
            $this->discoveryItems->add($location, $class);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $class) {
            $this->container->addResettable($class);
        }
    }
}
