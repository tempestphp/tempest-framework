<?php

declare(strict_types=1);

namespace Tempest\Log;

use Tempest\Container\HasTag;

interface LogConfig extends HasTag
{
    /**
     * An optional prefix displayed in all log messages. By default, the current environment is used.
     */
    public ?string $prefix {
        get;
    }

    /**
     * The log channels to which log messages will be sent.
     *
     * @var LogChannel[]
     */
    public array $logChannels {
        get;
    }
}
