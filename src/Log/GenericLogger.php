<?php

declare(strict_types=1);

namespace Tempest\Log;

use Monolog\Level;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Stringable;
use Tempest\Container\Container;
use Tempest\Log\Channels\LogChannel;
use Tempest\Support\ArrayHelper;

final class GenericLogger implements LoggerInterface
{
    public function __construct(
        private LogConfig $logConfig,
        private Container $container,
        /** @var array<class-string, Monolog> $drivers */
        private array $drivers = [],
    ) {

    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Emergency, $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Alert, $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Critical, $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Error, $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Warning, $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Notice, $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Info, $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->writeLog(Level::Debug, $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $level = Level::tryFrom($level) ?? Level::Info;

        $this->writeLog($level, $message, $context);
    }

    private function writeLog(Level $level, string $message, array $context): void
    {
        $this->resolveDriver($this->logConfig->channel, $level)->log($level, $message, $context);
    }

    private function resolveDriver(string $channelName, Level $level): Monolog
    {
        if (isset($this->drivers[$channelName])) {
            return $this->drivers[$channelName];
        }

        /** @var LogChannel $channel */
        $channel = $this->container->get($channelName);

        $config = $this->logConfig->channelsConfig[$channelName] ?? [];

        return $this->drivers[$channelName] = new Monolog(
            name: $this->logConfig->prefix,
            handlers: ArrayHelper::wrap($channel->handler($level, $config)),
            processors: ArrayHelper::wrap($channel->processor($config)),
        );
    }
}
