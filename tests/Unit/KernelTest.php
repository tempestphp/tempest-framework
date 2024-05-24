<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit;

use App\TestDependency;
use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryDiscovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\EventBusDiscovery;
use Tempest\Discovery\InitializerDiscovery;
use Tempest\Discovery\MapperDiscovery;
use Tempest\Kernel;

/**
 * @internal
 * @small
 */
class KernelTest extends TestCase
{
    public function test_discovery_boot(): void
    {
        $appConfig = new AppConfig(
            root: __DIR__ . '/../../',
            discoveryLocations: [
                new DiscoveryLocation('App\\', __DIR__ . '/../../app'),
            ],
        );

        $kernel = new Kernel($appConfig);

        $container = $kernel->init();

        $this->assertInstanceOf(Container::class, $container);

        $appConfig = $container->get(AppConfig::class);

        $this->assertCount(4, $appConfig->discoveryClasses);
        $this->assertSame(DiscoveryDiscovery::class, $appConfig->discoveryClasses[0]);
        $this->assertSame(EventBusDiscovery::class, $appConfig->discoveryClasses[1]);
        $this->assertSame(MapperDiscovery::class, $appConfig->discoveryClasses[2]);
        $this->assertSame(InitializerDiscovery::class, $appConfig->discoveryClasses[3]);

        $this->assertCount(2, $appConfig->discoveryLocations);
        $this->assertSame('Tempest\\', $appConfig->discoveryLocations[0]->namespace);
        $this->assertSame('App\\', $appConfig->discoveryLocations[1]->namespace);

        $test = $container->get(TestDependency::class);

        $this->assertInstanceOf(TestDependency::class, $test);
        $this->assertSame('test', $test->input);
    }
}
