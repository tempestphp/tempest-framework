<?php

namespace Tempest\Log\Config;

use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Log\LogLevel;

final class SimpleLogConfig implements LogConfig
{
    public array $channels {
        get => [
            new AppendLogChannel(
                path: $this->path,
                useLocking: $this->useLocking,
                minimumLogLevel: $this->minimumLogLevel,
                bubble: $this->bubble,
                filePermission: $this->filePermission,
            ),
        ];
    }

    /**
     * A basic logging configuration that appends all logs to a single file.
     *
     * @param string $path The log file path.
     * @param bool $useLocking Whether to try to lock log file before doing any writes.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write).
     */
    public function __construct(
        private(set) string $path,
        private(set) string $prefix,
        private(set) bool $useLocking = false,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) bool $bubble = true,
        private(set) ?int $filePermission = null,
    ) {}
}
