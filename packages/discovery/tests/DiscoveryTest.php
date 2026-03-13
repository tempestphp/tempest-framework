<?php

namespace Tempest\Discovery\Tests;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\Tests\Fixtures\ContainerWithoutAutowiring;
use Tempest\Discovery\Tests\Fixtures\MyDiscoveryClass;

final class DiscoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MyDiscoveryClass::$discoveredItem = null;
    }

    public function test_standalone_discovery(): void
    {
        $container = new GenericContainer();

        (new BootDiscovery(
            container: $container,
            config: new DiscoveryConfig(locations: [
                new DiscoveryLocation(
                    namespace: 'Tempest\Discovery\Tests\Fixtures',
                    path: __DIR__ . '/Fixtures',
                ),
            ]),
        ))();

        $this->assertNotNull(MyDiscoveryClass::$discoveredItem);
        $this->assertSame('check', MyDiscoveryClass::$discoveredItem->name);
    }

    public function test_discovery_with_other_container(): void
    {
        $container = new Container();

        (new BootDiscovery(
            container: $container,
            config: new DiscoveryConfig(locations: [
                new DiscoveryLocation(
                    namespace: 'Tempest\Discovery\Tests\Fixtures',
                    path: __DIR__ . '/Fixtures',
                ),
            ]),
        ))();

        $this->assertNotNull(MyDiscoveryClass::$discoveredItem);
        $this->assertSame('check', MyDiscoveryClass::$discoveredItem->name);
    }

    public function test_non_autowired_container_with_fallback(): void
    {
        $container = new ContainerWithoutAutowiring();

        (new BootDiscovery(
            container: $container,
            config: new DiscoveryConfig(locations: [
                new DiscoveryLocation(
                    namespace: 'Tempest\Discovery\Tests\Fixtures',
                    path: __DIR__ . '/Fixtures',
                ),
            ]),
        ))();

        $this->assertNotNull(MyDiscoveryClass::$discoveredItem);
        $this->assertSame('check', MyDiscoveryClass::$discoveredItem->name);
    }
}
