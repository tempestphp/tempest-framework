<?php

namespace Tempest\Log\Config;

use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\LogChannel;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;
use UnitEnum;

final class DailyLogConfig implements LogConfig
{
    public array $logChannels {
        get => [
            new DailyLogChannel(
                path: $this->path,
                maxFiles: $this->maxFiles,
                minimumLogLevel: $this->minimumLogLevel,
                lockFilesDuringWrites: $this->lockFilesDuringWrites,
                filePermission: $this->filePermission,
            ),
            ...$this->channels,
        ];
    }

    /**
     * A logging configuration that creates a new log file each day and retains a maximum number of files.
     *
     * @param string $path The base log file name.
     * @param int $maxFiles The maximal amount of files to keep (0 means unlimited)
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param array<LogChannel> $channels Additional channels to include in the configuration.
     * @param bool $lockFilesDuringWrites Whether to try to lock log file before doing any writes.
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param null|string $prefix An optional prefix displayed in all log messages. By default, the current environment is used.
     * @param null|UnitEnum|string $tag An optional tag to identify the logger instance associated to this configuration.
     */
    public function __construct(
        private(set) string $path,
        private(set) int $maxFiles = 31,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) array $channels = [],
        private(set) bool $lockFilesDuringWrites = false,
        private(set) ?int $filePermission = null,
        private(set) ?string $prefix = null,
        private(set) null|UnitEnum|string $tag = null,
    ) {}
}
