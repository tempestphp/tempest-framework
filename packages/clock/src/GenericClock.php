<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Psr\Clock\ClockInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\DateTime\Timestamp;

use const Tempest\DateTime\MILLISECONDS_PER_SECOND;

final class GenericClock implements Clock
{
    public function toPsrClock(): ClockInterface
    {
        return new PsrClock($this);
    }

    public function now(): DateTimeInterface
    {
        return DateTime::now();
    }

    public function timestamp(): Timestamp
    {
        return Timestamp::monotonic();
    }

    public function seconds(): int
    {
        return Timestamp::monotonic()->getSeconds();
    }

    public function milliseconds(): int
    {
        return Timestamp::monotonic()->getMilliseconds();
    }

    public function sleep(int|Duration $milliseconds): void
    {
        usleep(match (true) {
            is_int($milliseconds) => $milliseconds * MILLISECONDS_PER_SECOND,
            $milliseconds instanceof Duration => (int) $milliseconds->getTotalMicroseconds(),
        });
    }
}
