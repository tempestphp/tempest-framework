<?php

namespace Tempest\Testing;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class TestsDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly TestsConfig $testConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if (str_contains($location->path, '/vendor/') || str_contains($location->path, '\\vendor\\')) {
            return;
        }

        foreach ($class->getPublicMethods() as $method) {
            if ($method->hasAttribute(Test::class)) {
                $this->discoveryItems->add($location, $method);
            }
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $handler) {
            $this->testConfig->addTest($handler);
        }
    }
}