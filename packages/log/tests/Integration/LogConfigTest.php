<?php

declare(strict_types=1);

namespace Tempest\Log\Tests\Integration;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Log\LogConfig;

use function Tempest\root_path;

/**
 * @internal
 */
final class LogConfigTest extends FrameworkIntegrationTestCase
{
    public function test_log_path_defaults(): void
    {
        $logConfig = $this->container->get(LogConfig::class);

        $this->assertSame(root_path('log/debug.log'), $logConfig->debugLogPath);
    }
}
