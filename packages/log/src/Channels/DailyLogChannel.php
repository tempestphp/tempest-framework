<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Tempest\Log\FileHandlers\RotatingFileHandler;
use Tempest\Log\LogChannel;
use Tempest\Log\LogLevel;

final readonly class DailyLogChannel implements LogChannel
{
    /**
     * This channel writes logs to a file that is rotated daily.
     *
     * @param string $path The base log file name.
     * @param int $maxFiles The maximal amount of files to keep (0 means unlimited)
     * @param bool $lockFilesDuringWrites Whether to try to lock log file before doing any writes.
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param null|int $filePermission Optional file permissions (default (0644) are only for owner read/write)
     */
    public function __construct(
        private string $path,
        private int $maxFiles = 31,
        private LogLevel $minimumLogLevel = LogLevel::DEBUG,
        private bool $lockFilesDuringWrites = false,
        private bool $bubble = true,
        private ?int $filePermission = null,
    ) {}

    public function getHandlers(Level $level): array
    {
        if (! $this->minimumLogLevel->includes(LogLevel::fromMonolog($level))) {
            return [];
        }

        return [
            new RotatingFileHandler(
                filename: $this->path,
                maxFiles: $this->maxFiles,
                level: $level,
                bubble: $this->bubble,
                filePermission: $this->filePermission,
                useLocking: $this->lockFilesDuringWrites,
                dateFormat: RotatingFileHandler::FILE_PER_DAY,
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
