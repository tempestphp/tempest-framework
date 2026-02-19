<?php

namespace Tempest\Log\Config;

use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogChannel;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;
use UnitEnum;

final class SimpleLogConfig implements LogConfig
{
    public array $logChannels {
        get => [
            new AppendLogChannel(
                path: $this->path,
                useLocking: $this->useLocking,
                minimumLogLevel: $this->minimumLogLevel,
                filePermission: $this->filePermission,
            ),
            ...$this->channels,
        ];
    }

    /**
     * A basic logging configuration that appends all logs to a single file.
     *
     * @param string $path The log file path.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param array<LogChannel> $channels Additional channels to include in the configuration.
     * @param bool $useLocking Whether to try to lock log file before doing any writes.
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write).
     * @param null|string $prefix An optional prefix displayed in all log messages. By default, the current environment is used.
     * @param null|UnitEnum|string $tag An optional tag to identify the logger instance associated to this configuration.
     */
    public function __construct(
        private(set) string $path,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) array $channels = [],
        private(set) bool $useLocking = false,
        private(set) ?int $filePermission = null,
        private(set) ?string $prefix = null,
        private(set) null|UnitEnum|string $tag = null,
    ) {}
}
