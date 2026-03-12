<?php

namespace Tempest\Discovery\Tests;

use DI\Container;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\Registry;
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
            registry: new Registry(locations: [
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
            registry: new Registry(locations: [
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
