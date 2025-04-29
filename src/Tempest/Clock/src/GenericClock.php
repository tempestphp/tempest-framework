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

    public function timestamp(): int
    {
        return Timestamp::monotonic()->getSeconds();
    }

    public function timestampMs(): int
    {
        return Timestamp::monotonic()->getMilliseconds();
    }

    public function sleep(int|Duration $milliseconds): void
    {
        if ($milliseconds instanceof Duration) {
            $milliseconds = (int) $milliseconds->getTotalMilliseconds();
        }

        usleep($milliseconds * MILLISECONDS_PER_SECOND);
    }
}
