<?php

namespace Tests\Tempest\Fixtures;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class GlobalHiddenDiscovery implements Discovery
{
    public static bool $discovered = false;

    use IsDiscovery;

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
    }

    public function apply(): void
    {
        self::$discovered = true;
    }
}
