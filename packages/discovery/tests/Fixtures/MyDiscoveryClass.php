<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class MyDiscoveryClass implements Discovery
{
    use IsDiscovery;

    public static ?DiscoveredItem $discoveredItem = null;

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->is(DiscoveredItem::class)) {
            $this->discoveryItems->add($location, $class);
        }
    }

    public function apply(): void
    {
        /** @var ClassReflector $class */
        foreach ($this->discoveryItems as $class) {
            self::$discoveredItem = $class->newInstanceArgs(['name' => 'check']);
        }
    }
}
