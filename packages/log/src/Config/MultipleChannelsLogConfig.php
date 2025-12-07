<?php

namespace Tempest\Log\Config;

use Tempest\Log\LogChannel;
use Tempest\Log\LogConfig;
use UnitEnum;

final class MultipleChannelsLogConfig implements LogConfig
{
    public array $logChannels {
        get => $this->channels;
    }

    /**
     * A logging configuration that uses multiple log channels.
     *
     * @param LogChannel[] $channels The log channels to which log messages will be sent.
     * @param null|string $prefix An optional prefix displayed in all log messages. By default, the current environment is used.
     * @param null|UnitEnum|string $tag An optional tag to identify the logger instance associated to this configuration.
     */
    public function __construct(
        private(set) array $channels,
        private(set) ?string $prefix,
        private(set) null|UnitEnum|string $tag = null,
    ) {}
}
