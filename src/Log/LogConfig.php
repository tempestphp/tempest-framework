<?php

declare(strict_types=1);

namespace Tempest\Log;

final class LogConfig
{
    public function __construct(
        /** @var LogChannel[] */
        public array $channels = [],
        public string $prefix = 'tempest',
        public ?string $debugLogPath = null,
    ) {
    }
}
