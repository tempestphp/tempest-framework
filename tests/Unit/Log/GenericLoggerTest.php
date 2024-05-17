<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Log;

use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\GenericLogger;
use Tempest\Log\LogConfig;
use Tests\Tempest\IntegrationTest;

/**
 * @internal
 * @small
 */
final class GenericLoggerTest extends IntegrationTest
{
    public function test_append_log_channel_works(): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';

        $config = new LogConfig(
            channelsConfig: [
                AppendLogChannel::class => [
                  'path' => $filePath,
              ],
            ],
            channel: AppendLogChannel::class,
        );

        $logger = new GenericLogger(
            $config,
            $this->container,
        );

        $logger->info('test');

        $this->assertFileExists($filePath);

        $this->assertStringContainsString('test', file_get_contents($filePath));
    }

    public function test_daily_log_channel_works(): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';

        $config = new LogConfig(
            channelsConfig: [
                DailyLogChannel::class => [
                    'path' => $filePath,
                ],
            ],
            channel: DailyLogChannel::class
        );

        $logger = new GenericLogger(
            $config,
            $this->container,
        );

        $logger->info('test {a}', ['test']);

        $fileName = 'logs/tempest-' . date('Y-m-d') . '.log';

        $this->assertFileExists($fileName);

        $this->assertStringContainsString('test', file_get_contents($fileName));
    }
}
