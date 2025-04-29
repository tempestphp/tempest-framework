<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Drift\FrameworkIntegrationTestCase;

use function Tempest\Support\str;

/**
 * @internal
 */
final class DiscoveryStatusCommandTest extends FrameworkIntegrationTestCase
{
    public function test_discovery_status_command(): void
    {
        $output = $this->console->call('discovery:status -cl');

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $output->assertContains(basename(str_replace('\\', '/', $discoveryClass)));
        }

        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            // @TODO(aidan-casey): remove the src/ directory.
            $output->assertContains(str(realpath($discoveryLocation->path))->afterLast(['src/', 'packages/', 'vendor/', 'tests/'])->toString());
        }
    }
}
