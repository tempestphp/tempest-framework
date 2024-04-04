<?php

namespace Tests\Tempest\Unit;

use App\TestDependency;
use PHPUnit\Framework\TestCase;
use Tempest\Container\Container;
use Tempest\CoreConfig;
use Tempest\Discovery\DiscoveryDiscovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\InitializerDiscovery;
use Tempest\Kernel;

class KernelTest extends TestCase
{
    public function test_discovery_boot(): void
    {
        $coreConfig = new CoreConfig(
            root: __DIR__ . '/../../',
            discoveryLocations: [
                new DiscoveryLocation('App\\', __DIR__ . '/../../app'),
            ],
        );

        $kernel = new Kernel($coreConfig);

        $container = $kernel->init();

        $this->assertInstanceOf(Container::class, $container);

        $coreConfig = $container->get(CoreConfig::class);

        $this->assertCount(2, $coreConfig->discoveryClasses);
        $this->assertSame(DiscoveryDiscovery::class, $coreConfig->discoveryClasses[0]);
        $this->assertSame(InitializerDiscovery::class, $coreConfig->discoveryClasses[1]);

        $this->assertCount(2, $coreConfig->discoveryLocations);
        $this->assertSame('Tempest\\', $coreConfig->discoveryLocations[0]->namespace);
        $this->assertSame('App\\', $coreConfig->discoveryLocations[1]->namespace);

        $test = $container->get(TestDependency::class);

        $this->assertInstanceOf(TestDependency::class, $test);
        $this->assertSame('test', $test->input);
    }
}
