<?php

namespace Tempest\Log\Config;

use Tempest\Log\LogConfig;

final class NullLogConfig implements LogConfig
{
    public array $channels {
        get => [];
    }

    /**
     * A logging configuration that does not log anything.
     */
    public function __construct(
        private(set) string $prefix = 'tempest',
    ) {}
}
