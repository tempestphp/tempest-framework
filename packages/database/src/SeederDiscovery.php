<?php

namespace Tempest\Database;

use Tempest\Database\Config\SeederConfig;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class SeederDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private SeederConfig $seederConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(DatabaseSeeder::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $discoveryItem) {
            $this->seederConfig->seeders[] = $discoveryItem;
        }
    }
}