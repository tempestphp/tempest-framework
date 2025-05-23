<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Psr\Clock\ClockInterface;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\DateTime\Timestamp;

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
     * Returns the current timestamp.
     */
    public function timestamp(): Timestamp;

    /**
     * Returns the current UNIX timestamp in seconds.
     */
    public function seconds(): int;

    /**
     * Returns the current timestamp in milliseconds.
     */
    public function milliseconds(): int;

    /**
     * Sleeps for the given number of milliseconds.
     */
    public function sleep(int|Duration $milliseconds): void;
}
