<?php

declare(strict_types=1);

namespace Tempest\Discovery\Stubs;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Reflection\ClassReflector;
use Tempest\Discovery\IsDiscovery;

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
    
    public function apply(): void
    {
        foreach ($this->discoveryItems as $class) {
            // Do something with the discovered class
        }
    }
}
