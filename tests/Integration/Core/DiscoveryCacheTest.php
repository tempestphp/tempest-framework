<?php

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\CouldNotStoreDiscoveryCache;
use Tempest\Core\DiscoveryCache;
use Tempest\Discovery\DiscoveryLocation;
use Tests\Tempest\Integration\Core\Fixtures\TestDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\reflect;

final class DiscoveryCacheTest extends FrameworkIntegrationTestCase
{
    public function test_exception_with_unserializable_discovery_items(): void
    {
        $this->assertException(CouldNotStoreDiscoveryCache::class, function () {
            $discoveryCache = $this->container->get(DiscoveryCache::class);

            $location = new DiscoveryLocation('Test\\', '.');
            $discovery = new TestDiscovery();
            $discovery->discover($location, reflect($this));

            $discoveryCache->store($location, [
                $discovery,
            ]);
        });
    }
}
