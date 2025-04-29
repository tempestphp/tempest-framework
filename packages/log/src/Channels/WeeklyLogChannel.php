<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Tempest\Log\FileHandlers\RotatingFileHandler;
use Tempest\Log\LogChannel;

final readonly class WeeklyLogChannel implements LogChannel
{
    public function __construct(
        private string $path,
        private int $maxFiles = 5,
        private bool $bubble = true,
        private ?int $filePermission = null,
        private bool $useLocking = false,
    ) {}

    public function getHandlers(Level $level): array
    {
        return [
            new RotatingFileHandler(
                filename: $this->path,
                maxFiles: $this->maxFiles,
                level: $level,
                bubble: $this->bubble,
                filePermission: $this->filePermission,
                useLocking: $this->useLocking,
                dateFormat: RotatingFileHandler::FILE_PER_WEEK,
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
