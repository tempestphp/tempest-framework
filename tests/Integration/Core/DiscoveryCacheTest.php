<?php

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\CouldNotStoreDiscoveryCache;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryLocation;
use Tests\Tempest\Integration\Core\Fixtures\TestDiscovery;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Reflection\reflect;

final class DiscoveryCacheTest extends FrameworkIntegrationTestCase
{
    #[PostCondition]
    protected function cleanup(): void
    {
        putenv('ENVIRONMENT=testing');
        putenv('DISCOVERY_CACHE=true');
    }

    #[Test]
    public function exception_with_unserializable_discovery_items(): void
    {
        $this->assertException(CouldNotStoreDiscoveryCache::class, function (): void {
            $discoveryCache = $this->container->get(DiscoveryCache::class);

            $location = new DiscoveryLocation('Test\\', '.');
            $discovery = new TestDiscovery();
            $discovery->discover($location, reflect($this));

            $discoveryCache->store($location, [
                $discovery,
            ]);
        });
    }

    #[Test]
    public function partial_locally(): void
    {
        putenv('ENVIRONMENT=local');
        putenv('DISCOVERY_CACHE=null');

        $this->assertSame(DiscoveryCacheStrategy::PARTIAL, DiscoveryCacheStrategy::resolveFromEnvironment());
    }

    #[Test]
    public function overridable_locally(): void
    {
        putenv('ENVIRONMENT=local');
        putenv('DISCOVERY_CACHE=false');

        $this->assertSame(DiscoveryCacheStrategy::NONE, DiscoveryCacheStrategy::resolveFromEnvironment());
    }

    #[Test]
    public function enabled_in_production(): void
    {
        putenv('ENVIRONMENT=production');
        putenv('DISCOVERY_CACHE=null');

        $this->assertSame(DiscoveryCacheStrategy::FULL, DiscoveryCacheStrategy::resolveFromEnvironment());
    }

    #[Test]
    public function enabled_in_staging(): void
    {
        putenv('ENVIRONMENT=staging');
        putenv('DISCOVERY_CACHE=null');

        $this->assertSame(DiscoveryCacheStrategy::FULL, DiscoveryCacheStrategy::resolveFromEnvironment());
    }

    #[Test]
    public function overridable_in_production(): void
    {
        putenv('ENVIRONMENT=production');
        putenv('DISCOVERY_CACHE=partial');

        $this->assertSame(DiscoveryCacheStrategy::PARTIAL, DiscoveryCacheStrategy::resolveFromEnvironment());
    }
}
