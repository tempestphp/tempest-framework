<?php

namespace Tests\Tempest\Integration\Core\Fixtures;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;

#[SkipDiscovery]
final class ManualTestDiscovery implements Discovery
{
    use IsDiscovery;

    private bool $shouldDiscover = false;

    public function __construct(
        private readonly ManualTestDiscoveryDependency $dependency,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->getName() === ManualTestDiscoveryDependency::class) {
            $this->shouldDiscover = true;
        }
    }

    public function apply(): void
    {
        $this->dependency->discovered = $this->shouldDiscover;
    }
}
