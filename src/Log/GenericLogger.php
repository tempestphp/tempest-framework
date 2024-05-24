<?php

declare(strict_types=1);

namespace Tempest\Log;

use Monolog\Level;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Stringable;

final class GenericLogger implements LoggerInterface
{
    /** @var array<class-string, Monolog> */
    private array $drivers = [];

    public function __construct(
        private readonly LogConfig $logConfig,
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
        foreach ($this->logConfig->channels as $channel) {
            $this->resolveDriver($channel, $level)->log($level, $message, $context);
        }
    }

    private function resolveDriver(LogChannel $channel, Level $level): Monolog
    {
        if (! isset($this->drivers[$channel::class])) {
            $this->drivers[$channel::class] = new Monolog(
                name: $this->logConfig->prefix,
                handlers: $channel->getHandlers($level),
                processors: $channel->getProcessors(),
            );
        }

        return $this->drivers[$channel::class];
    }
}
