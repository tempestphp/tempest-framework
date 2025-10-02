<?php

namespace Tempest\Log\Config;

use Tempest\Log\LogChannel;
use Tempest\Log\LogConfig;

final class MultipleChannelsLogConfig implements LogConfig
{
    /**
     * A logging configuration that uses multiple log channels.
     *
     * @param LogChannel[] $channels The log channels to which log messages will be sent.
     * @param string $prefix A descriptive name attached to all log messages.
     */
    public function __construct(
        private(set) array $channels,
        private(set) string $prefix,
    ) {}
}
