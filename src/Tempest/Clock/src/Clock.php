<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Psr\Clock\ClockInterface;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

interface Clock
{
    /**
     * Returns the current date and time.
     */
    public function now(): DateTimeInterface;

    /**
     * Returns the same instance as a PSR-7 Clock Interface.
     */
    public function toPsrClock(): ClockInterface;

    /**
     * Returns the current timestamp in seconds.
     */
    public function timestamp(): int;

    /**
     * Returns the current timestamp in milliseconds.
     */
    public function timestampMs(): int;

    /**
     * Sleeps for the given number of milliseconds.
     */
    public function sleep(int|Duration $milliseconds): void;
}
