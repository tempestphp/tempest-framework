<?php

declare(strict_types=1);

namespace Tempest\Log\Channels;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;

final class DailyLogChannel implements LogChannel
{
    public function handler(Level $level, array $config): HandlerInterface
    {
        return new RotatingFileHandler(
            filename: $config['path'] ?? 'logs/tempest.log',
            maxFiles: $config['rotation'] ?? 30,
            level: $level,
            bubble: $config['bubble'] ?? true,
            filePermission: $config['file_permission'] ?? null,
            useLocking: $config['use_locking'] ?? false
        );
    }

    public function processor(array $config): array
    {
        return [
            new PsrLogMessageProcessor(),
        ];
    }
}
