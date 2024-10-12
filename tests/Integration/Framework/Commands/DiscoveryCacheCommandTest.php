<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DiscoveryCacheCommandTest extends FrameworkIntegrationTestCase
{
    public function test_it_clears_discovery_cache(): void
    {
        $this->markTestSkipped('Need to reimplement');
        //        $this->console->call('discovery:cache');
    }
}
