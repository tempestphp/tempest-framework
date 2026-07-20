<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\str;

/**
 * @internal
 */
final class DiscoveryStatusCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function discovery_status_command(): void
    {
        $output = $this->console->call('discovery:status -cl');

        foreach ($this->kernel->discoveryConfig->classes as $discoveryClass) {
            $output->assertContains(basename(str_replace('\\', '/', $discoveryClass)));
        }

        foreach ($this->kernel->discoveryConfig->locations as $discoveryLocation) {
            // @TODO(aidan-casey): remove the src/ directory.
            $output->assertContains(str(realpath($discoveryLocation->path))->afterLast(['src/', 'packages/', 'vendor/', 'tests/'])->toString());
        }
    }
}
