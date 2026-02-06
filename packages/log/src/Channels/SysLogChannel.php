<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Tempest\Log\LogChannel;
use Tempest\Log\LogLevel;

final readonly class SysLogChannel implements LogChannel
{
    /**
     * @param string $identity The identity string to use for each log message. This is typically the application name.
     * @param int $facility The syslog facility to use. See https://www.php.net/manual/en/function.openlog.php for available options.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not.
     * @param int $flags Options for the openlog system call. See https://www.php.net/manual/en/function.openlog.php
     */
    public function __construct(
        private string $identity,
        private int $facility = LOG_USER,
        private LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private bool $bubble = true,
        private int $flags = LOG_PID,
    ) {}

    public function getHandlers(Level $level): array
    {
        if (! $this->minimumLogLevel->includes(LogLevel::fromMonolog($level))) {
            return [];
        }

        return [
            new SyslogHandler(
                ident: $this->identity,
                facility: $this->facility,
                level: $level,
                bubble: $this->bubble,
                logopts: $this->flags,
            ),
        ];
    }

    public function getProcessors(): array
    {
        return [
            new PsrLogMessageProcessor(),
        ];
    }
}
