<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Core\AppConfig;
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

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $output->assertContains($discoveryClass);
        }

        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $output->assertContains($discoveryLocation->path);
        }
    }
}
