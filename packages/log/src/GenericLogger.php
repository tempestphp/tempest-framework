<?php

declare(strict_types=1);

namespace Tempest\Log;

use Monolog\Level as MonologLogLevel;
use Monolog\Logger as Monolog;
use Psr\Log\LogLevel as PsrLogLevel;
use Stringable;
use Tempest\EventBus\EventBus;

final class GenericLogger implements Logger
{
    /** @var array<int, Monolog> */
    private array $drivers = [];

    public function __construct(
        private readonly LogConfig $logConfig,
        private readonly EventBus $eventBus,
    ) {}

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /** @param MonologLogLevel|LogLevel|string $level */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (! ($level instanceof MonologLogLevel)) {
            $level = match ($level) {
                LogLevel::EMERGENCY, PsrLogLevel::EMERGENCY => MonologLogLevel::Emergency,
                LogLevel::ALERT, PsrLogLevel::ALERT => MonologLogLevel::Alert,
                LogLevel::CRITICAL, PsrLogLevel::CRITICAL => MonologLogLevel::Critical,
                LogLevel::ERROR, PsrLogLevel::ERROR => MonologLogLevel::Error,
                LogLevel::WARNING, PsrLogLevel::WARNING => MonologLogLevel::Warning,
                LogLevel::NOTICE, PsrLogLevel::NOTICE => MonologLogLevel::Notice,
                LogLevel::INFO, PsrLogLevel::INFO => MonologLogLevel::Info,
                LogLevel::DEBUG, PsrLogLevel::DEBUG => MonologLogLevel::Debug,
                default => MonologLogLevel::Info,
            };
        }

        $this->writeLog($level, $message, $context);

        $this->eventBus->dispatch(new MessageLogged(LogLevel::fromMonolog($level), $message, $context));
    }

    private function writeLog(MonologLogLevel $level, string $message, array $context): void
    {
        foreach ($this->logConfig->channels as $channel) {
            $this->resolveDriver($channel, $level)->log($level, $message, $context);
        }
    }

    private function resolveDriver(LogChannel $channel, MonologLogLevel $level): Monolog
    {
        if (! isset($this->drivers[spl_object_id($channel)])) {
            $this->drivers[spl_object_id($channel)] = new Monolog(
                name: $this->logConfig->prefix,
                handlers: $channel->getHandlers($level),
                processors: $channel->getProcessors(),
            );
        }

        return $this->drivers[spl_object_id($channel)];
    }
}
