<?php

namespace Tempest\Testing\Discovery;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Testing\Config\TestConfig;
use Tempest\Testing\Test;

#[SkipDiscovery]
final class TestDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly TestConfig $testConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            if ($test = $method->getAttribute(Test::class)) {
                $this->discoveryItems->add($location, [$test, $method]);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$test, $handler]) {
            $this->testConfig->addTest($test, $handler);
        }
    }
}