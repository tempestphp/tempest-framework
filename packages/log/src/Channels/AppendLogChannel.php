<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Tempest\Log\LogChannel;
use Tempest\Log\LogLevel;

final readonly class AppendLogChannel implements LogChannel
{
    /**
     * @param string $path The log file path.
     * @param bool $useLocking Whether to try to lock log file before doing any writes.
     * @param LogLevel $minimumLogLevel The minimum log level to record.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write).
     */
    public function __construct(
        private(set) string $path,
        private(set) bool $useLocking = false,
        private(set) LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private(set) bool $bubble = true,
        private(set) ?int $filePermission = null,
    ) {}

    public function getHandlers(Level $level): array
    {
        if (! $this->minimumLogLevel->includes(LogLevel::fromMonolog($level))) {
            return [];
        }

        return [
            new StreamHandler(
                stream: $this->path,
                level: $level,
                bubble: $this->bubble,
                filePermission: $this->filePermission,
                useLocking: $this->useLocking,
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
