<?php

namespace Tempest\Clock;

use Tempest\DateTime\DateTimeInterface;

use function Tempest\get;

/**
 * Get the current date and time as a {@see \Tempest\DateTime\DateTimeInterface} object.
 */
function now(): DateTimeInterface
{
    return get(Clock::class)->now();
}
