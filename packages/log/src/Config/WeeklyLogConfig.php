<?php

namespace Tempest\Log\Config;

use Tempest\Log\Channels\WeeklyLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;

final class WeeklyLogConfig implements LogConfig
{
    public array $channels {
        get => [
            new WeeklyLogChannel(
                path: $this->path,
                maxFiles: $this->maxFiles,
                lockFilesDuringWrites: $this->lockFilesDuringWrites,
                minimumLogLevel: $this->minimumLogLevel,
                bubble: $this->bubble,
                filePermission: $this->filePermission,
            ),
        ];
    }

    /**
     * A logging configuration that creates a new log file each week and retains a maximum number of files.
     *
     * @param string $path The base log file name.
     * @param int $maxFiles The maximal amount of files to keep.
     * @param string $prefix A descriptive name attached to all log messages.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param bool $lockFilesDuringWrites Whether to try to lock log file before doing any writes.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not.
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write).
     */
    public function __construct(
        private(set) string $path,
        private(set) string $prefix,
        private(set) int $maxFiles = 5,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) bool $lockFilesDuringWrites = false,
        private(set) bool $bubble = true,
        private(set) ?int $filePermission = null,
    ) {}
}
