<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Framework\Application\AppConfig;
use Tempest\Framework\Application\Kernel;
use Tests\Tempest\Fixtures\TestDependency;

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
                new DiscoveryLocation('Tests\\Tempest\\Fixtures\\', __DIR__ . '/../Fixtures/'),
            ],
        );

        $kernel = new Kernel($appConfig);

        $container = $kernel->init();

        $this->assertInstanceOf(Container::class, $container);

        $appConfig = $container->get(AppConfig::class);


        // TODO: Clean this up, it doesn't scale well.
        $this->assertCount(10, $appConfig->discoveryClasses);

        /**
         * This was commented by Aidan Casey on July 19, 2024. As my previous
         * comment mentions, this doesn't scale well and I will look to fix this in a future PR.
         */
        //        $this->assertSame(DiscoveryDiscovery::class, $appConfig->discoveryClasses[0]);
        //        $this->assertSame(MigrationDiscovery::class, $appConfig->discoveryClasses[1]);
        //        $this->assertSame(EventBusDiscovery::class, $appConfig->discoveryClasses[2]);
        //        $this->assertSame(CommandBusDiscovery::class, $appConfig->discoveryClasses[3]);
        //        $this->assertSame(MapperDiscovery::class, $appConfig->discoveryClasses[4]);
        //        $this->assertSame(InitializerDiscovery::class, $appConfig->discoveryClasses[5]);
        //        $this->assertSame(RouteDiscovery::class, $appConfig->discoveryClasses[6]);
        //        $this->assertSame(ViewComponentDiscovery::class, $appConfig->discoveryClasses[7]);
        //        $this->assertSame(ScheduleDiscovery::class, $appConfig->discoveryClasses[8]);
        //        $this->assertSame(EventBusDiscovery::class, $appConfig->discoveryClasses[9]);
        //        $this->assertSame(ScheduleDiscovery::class, $appConfig->discoveryClasses[10]);
        //        $this->assertSame(ConsoleCommandDiscovery::class, $appConfig->discoveryClasses[11]);

        //        $this->assertCount(2, $appConfig->discoveryLocations);
        //        $this->assertSame('Tempest\\', $appConfig->discoveryLocations[0]->namespace);
        //        $this->assertSame('Tests\\Tempest\\Fixtures\\', $appConfig->discoveryLocations[1]->namespace);

        $test = $container->get(TestDependency::class);

        $this->assertInstanceOf(TestDependency::class, $test);
        $this->assertSame('test', $test->input);
    }
}
