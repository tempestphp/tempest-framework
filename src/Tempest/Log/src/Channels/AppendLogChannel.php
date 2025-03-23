<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Tempest\Log\LogChannel;

final readonly class AppendLogChannel implements LogChannel
{
    public function __construct(
        private string $path,
        private bool $bubble = true,
        private ?int $filePermission = null,
        private bool $useLocking = false,
    ) {}

    public function getHandlers(Level $level): array
    {
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

    public function getPath(): string
    {
        return $this->path;
    }
}
