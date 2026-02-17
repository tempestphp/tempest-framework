<?php

namespace Tempest\Log\Config;

use Tempest\Log\Channels\SysLogChannel;
use Tempest\Log\LogChannel;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;
use UnitEnum;

final class SysLogConfig implements LogConfig
{
    public array $logChannels {
        get => [
            new SysLogChannel(
                identity: $this->identity,
                facility: $this->facility,
                minimumLogLevel: $this->minimumLogLevel,
                bubble: $this->bubble,
                flags: $this->flags,
            ),
            ...$this->channels,
        ];
    }

    /**
     * A logging configuration for sending log messages to the system logger (syslog).
     *
     * @param string $identity The identity string to use for each log message. This is typically the application name.
     * @param int $facility The syslog facility to use. See https://www.php.net/manual/en/function.openlog.php for available options.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not.
     * @param int $flags Options for the openlog system call. See https://www.php.net/manual/en/function.openlog.php
     * @param array<LogChannel> $channels Additional channels to include in the configuration.
     * @param null|string $prefix An optional prefix displayed in all log messages. By default, the current environment is used.
     * @param null|UnitEnum|string $tag An optional tag to identify the logger instance associated to this configuration.
     */
    public function __construct(
        private(set) string $identity,
        private(set) int $facility = LOG_USER,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) bool $bubble = true,
        private(set) int $flags = LOG_PID,
        private(set) array $channels = [],
        private(set) ?string $prefix = null,
        private(set) null|UnitEnum|string $tag = null,
    ) {}
}
