<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Commands;

use Tempest\AppConfig;
use Tests\Tempest\TestCase;
use Tests\Tempest\Unit\Console\Fixtures\MyDiscovery;

class DiscoveryClearCommandTest extends TestCase
{
    /** @test */
    public function it_clears_discovery_cache()
    {
        $appConfig = $this->container->get(AppConfig::class);

        MyDiscovery::$cacheCleared = false;

        $appConfig->discoveryClasses = [MyDiscovery::class];

        $this->console('discovery:clear');

        $this->assertTrue(MyDiscovery::$cacheCleared);
    }
}
