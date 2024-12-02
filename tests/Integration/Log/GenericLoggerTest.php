<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Log;

use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel as PsrLogLevel;
use ReflectionClass;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\Channels\WeeklyLogChannel;
use Tempest\Log\GenericLogger;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;
use Tempest\Log\MessageLogged;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericLoggerTest extends FrameworkIntegrationTestCase
{
    public function test_append_log_channel_works(): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';

        $config = new LogConfig(
            channels: [
                new AppendLogChannel($filePath),
            ],
        );


        $logger->info('test');

        $this->assertFileExists($filePath);

        $this->assertStringContainsString('test', file_get_contents($filePath));
    }

    protected function tearDown(): void
    {
        $files = glob(__DIR__ . '/logs/*.log');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function test_daily_log_channel_works(): void
    {
        $filePath = __DIR__ . '/logs/tempest-' . date('Y-m-d') . '.log';

        $config = new LogConfig(
            channels: [
                new DailyLogChannel(__DIR__ . '/logs/tempest.log'),
            ],
        );


        $logger->info('test');

        $this->assertFileExists($filePath);

        $this->assertStringContainsString('test', file_get_contents($filePath));
    }

    public function test_weekly_log_channel_works(): void
    {
        $filePath = __DIR__ . '/logs/tempest-' . date('Y-W') . '.log';

        $config = new LogConfig(
            channels: [
                new WeeklyLogChannel(__DIR__ . '/logs/tempest.log'),
            ],
        );


        $logger->info('test');

        $this->assertFileExists($filePath);

        $this->assertStringContainsString('test', file_get_contents($filePath));
    }

    public function test_multiple_same_log_channels_works(): void
    {
        $filePath = __DIR__ . '/logs/multiple-tempest1.log';
        $secondFilePath = __DIR__ . '/logs/multiple-tempest2.log';

        $config = new LogConfig(
            channels: [
                new AppendLogChannel($filePath),
                new AppendLogChannel($secondFilePath),
            ],
        );

        $logger->info('test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('test', file_get_contents($filePath));

        $this->assertFileExists($secondFilePath);
        $this->assertStringContainsString('test', file_get_contents($secondFilePath));
    }

    #[DataProvider('psrLogLevelProvider')]
    #[DataProvider('monologLevelProvider')]
    #[DataProvider('tempestLevelProvider')]
    public function test_log_levels(mixed $level, string $expected): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';
        $config = new LogConfig(
            prefix: 'tempest',
            channels: [
                new AppendLogChannel($filePath),
            ],
        );

        $logger = new GenericLogger($config);
        $logger->log($level, 'test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString("tempest." . $expected, file_get_contents($filePath));
    }

    public static function tempestLevelProvider(): array
    {
        return array_map(fn (LogLevel $level) => [$level, strtoupper($level->value)], LogLevel::cases());
    }

    public static function monologLevelProvider(): array
    {
        return array_map(fn (Level $level) => [$level, strtoupper($level->name)], Level::cases());
    }

    public static function psrLogLevelProvider(): array
    {
        $reflection = new ReflectionClass(PsrLogLevel::class);
        $levels = $reflection->getConstants();

        return array_map(fn (string $level) => [$level, strtoupper($level)], array_values($levels));
    }
}
