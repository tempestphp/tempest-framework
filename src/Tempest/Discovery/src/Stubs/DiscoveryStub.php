<?php

declare(strict_types=1);

namespace Tempest\Discovery\Stubs;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;

#[SkipDiscovery]
final class DiscoveryStub implements Discovery
{
    use IsDiscovery;

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (! $class->implements('MyClass::class')) {
            return;
        }

        $this->discoveryItems->add($location, $class);
    }

    /**
     * @mago-expect best-practices/no-empty-loop
     */
    public function apply(): void
    {
        foreach ($this->discoveryItems as $class) {
            // Do something with the discovered class
        }
    }
}
