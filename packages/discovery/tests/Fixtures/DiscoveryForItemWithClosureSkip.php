<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class DiscoveryForItemWithClosureSkip implements Discovery
{
    use IsDiscovery;

    public static bool $discovered = false;

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->is(ItemWithClosureSkip::class)) {
            self::$discovered = true;
        }
    }

    public function apply(): void
    {
        // TODO: Implement apply() method.
    }
}
