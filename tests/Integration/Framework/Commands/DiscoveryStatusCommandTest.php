<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Framework\Application\AppConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class DiscoveryStatusCommandTest extends FrameworkIntegrationTestCase
{
    public function test_discovery_status_command(): void
    {
        $output = $this->console->call('discovery:status');

        $appConfig = $this->container->get(AppConfig::class);

        foreach ($appConfig->discoveryClasses as $discoveryClass) {
            $output->assertContains($discoveryClass);
        }

        foreach ($appConfig->discoveryLocations as $discoveryLocation) {
            $output->assertContains($discoveryLocation->path);
        }
    }
}
