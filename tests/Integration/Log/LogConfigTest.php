<?php

declare(strict_types=1);

namespace Log;

use Tempest\Log\LogConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class LogConfigTest extends FrameworkIntegrationTestCase
{
    public function test_log_path_by_env(): void
    {
        $expectedDebugLogPath = 'log/debug.test.log';
        $expectedServerLogPath = 'log/server.test.log';

        $this->kernel->loadConfig();

        $logConfig = $this->container->get(LogConfig::class);

        $this->assertSame($expectedDebugLogPath, $logConfig->debugLogPath);
        $this->assertSame($expectedServerLogPath, $logConfig->serverLogPath);
    }
}
