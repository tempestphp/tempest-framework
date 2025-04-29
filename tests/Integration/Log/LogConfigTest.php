<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Log;

use Tempest\Log\LogConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
