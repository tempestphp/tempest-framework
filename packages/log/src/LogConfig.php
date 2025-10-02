<?php

declare(strict_types=1);

namespace Tempest\Log;

interface LogConfig
{
    /**
     * A descriptive name attached to all log messages.
     */
    public string $prefix {
        get;
    }

    /**
     * The log channels to which log messages will be sent.
     *
     * @var LogChannel[]
     */
    public array $channels {
        get;
    }
}
