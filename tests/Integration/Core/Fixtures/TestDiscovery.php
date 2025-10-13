<?php

namespace Tests\Tempest\Integration\Core\Fixtures;

use Exception;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class TestDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct()
    {
        $this->discoveryItems = new DiscoveryItems();
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        $class = new class {
            public function __serialize(): array
            {
                throw new Exception('Cannot serialize');
            }
        };

        $this->discoveryItems->add($location, $class);
    }

    public function apply(): void
    {
        // Nothing
    }
}
