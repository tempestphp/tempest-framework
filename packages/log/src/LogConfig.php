<?php

declare(strict_types=1);

namespace Tempest\Log;

final class LogConfig
{
    /**
     * @param LogChannel[] $channels
     * @param string $prefix A descriptive name attached to all log messages.
     */
    public function __construct(
        public array $channels = [],
        public string $prefix = 'tempest',
    ) {}
}
