<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeImmutable;

final class GenericClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }

    /**
     * Returns the unix timestamp of the current time in the given unit.
     *
     * @param TimeUnit $unit
     *
     * @return int
     */
    public function time(TimeUnit $unit = TimeUnit::SECOND): int
    {
        return (int) floor((microtime(true) * 1_000_000) / $unit->toMicroseconds());
    }

    public function sleep(int $time, TimeUnit $unit = TimeUnit::SECOND): void
    {
        usleep($time * $unit->toMicroseconds());
    }
}
