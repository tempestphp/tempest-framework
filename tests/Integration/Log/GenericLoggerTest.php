<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Log;

use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LogLevel as PsrLogLevel;
use ReflectionClass;
use Tempest\Core\Environment;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\Config\DailyLogConfig;
use Tempest\Log\Config\MultipleChannelsLogConfig;
use Tempest\Log\Config\NullLogConfig;
use Tempest\Log\Config\SimpleLogConfig;
use Tempest\Log\Config\WeeklyLogConfig;
use Tempest\Log\GenericLogger;
use Tempest\Log\LogLevel;
use Tempest\Log\MessageLogged;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class GenericLoggerTest extends FrameworkIntegrationTestCase
{
    private EventBus $bus {
        get => $this->container->get(EventBus::class);
    }

    private Environment $environment {
        get => $this->container->get(Environment::class);
    }

    #[PreCondition]
    protected function configure(): void
    {
        Filesystem\ensure_directory_empty(__DIR__ . '/logs');
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        Filesystem\delete_directory(__DIR__ . '/logs');
    }

    #[Test]
    public function simple_log_config(): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';

        $config = new SimpleLogConfig($filePath, prefix: 'tempest');

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('test', Filesystem\read_file($filePath));
    }

    #[Test]
    public function daily_log_config(): void
    {
        $clock = $this->clock();
        $filePath = __DIR__ . '/logs/tempest-' . date('Y-m-d') . '.log';
        $config = new DailyLogConfig(__DIR__ . '/logs/tempest.log', prefix: 'tempest');

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('test', Filesystem\read_file($filePath));

        $clock->plus(Duration::day());
        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');

        $clock->plus(Duration::days(2));
        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');
    }

    #[Test]
    public function weekly_log_config(): void
    {
        $filePath = __DIR__ . '/logs/tempest-' . date('Y-W') . '.log';
        $config = new WeeklyLogConfig(__DIR__ . '/logs/tempest.log', prefix: 'tempest');

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('test', Filesystem\read_file($filePath));
    }

    #[Test]
    public function multiple_same_log_channels(): void
    {
        $filePath = __DIR__ . '/logs/multiple-tempest1.log';
        $secondFilePath = __DIR__ . '/logs/multiple-tempest2.log';

        $config = new MultipleChannelsLogConfig(
            channels: [
                new AppendLogChannel($filePath),
                new AppendLogChannel($secondFilePath),
            ],
            prefix: 'tempest',
        );

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->info('test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('test', Filesystem\read_file($filePath));

        $this->assertFileExists($secondFilePath);
        $this->assertStringContainsString('test', Filesystem\read_file($secondFilePath));
    }

    #[Test]
    #[DataProvider('psrLogLevelProvider')]
    #[DataProvider('monologLevelProvider')]
    #[DataProvider('tempestLevelProvider')]
    public function log_levels(mixed $level, string $expected): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';
        $config = new SimpleLogConfig(
            path: $filePath,
            prefix: 'tempest',
        );

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->log($level, 'test');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('tempest.' . $expected, Filesystem\read_file($filePath));
    }

    #[Test]
    #[DataProvider('tempestLevelProvider')]
    public function message_logged_emitted(LogLevel $level, string $_expected): void
    {
        $eventBus = $this->container->get(EventBus::class);

        $eventBus->listen(function (MessageLogged $event) use ($level): void {
            $this->assertSame($level, $event->level);
            $this->assertSame('This is a log message of level: ' . $level->value, $event->message);
            $this->assertSame(['foo' => 'bar'], $event->context);
        });

        $logger = new GenericLogger(new NullLogConfig(), $this->environment, $this->bus);
        $logger->log($level, 'This is a log message of level: ' . $level->value, context: ['foo' => 'bar']);
    }

    #[Test]
    public function different_log_levels(): void
    {
        $filePath = __DIR__ . '/logs/tempest.log';
        $config = new SimpleLogConfig(
            path: $filePath,
            prefix: 'tempest',
        );

        $logger = new GenericLogger($config, $this->environment, $this->bus);
        $logger->critical('critical');
        $logger->debug('debug');

        $this->assertFileExists($filePath);
        $this->assertStringContainsString('critical', Filesystem\read_file($filePath));
        $this->assertStringContainsString('debug', Filesystem\read_file($filePath));
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
