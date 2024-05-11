<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

final class MockClock implements Clock
{
    private DateTimeImmutable $now;

    public function __construct(DateTimeInterface|string $now = 'now')
    {
        $this->now = $now instanceof DateTimeInterface
            ? DateTimeImmutable::createFromInterface($now)
            : new DateTimeImmutable($now);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function time(TimeUnit $unit = TimeUnit::SECOND): int
    {
        return (int) floor((($this->now->getTimestamp() * 1_000_000 + (int) $this->now->format('u')) / $unit->toMicroseconds()));
    }

    public function sleep(int $time, TimeUnit $unit = TimeUnit::SECOND): void
    {
        $this->now = $this->now->add(
            DateInterval::createFromDateString("$time $unit->value")
        );
    }

    public function changeTime(int $time, TimeUnit $unit = TimeUnit::SECOND): void
    {
        if ($time < 0) {
            $time = abs($time);
            $this->now = $this->now->sub(DateInterval::createFromDateString("$time $unit->value"));
        } else {
            $this->now = $this->now->add(DateInterval::createFromDateString("$time $unit->value"));
        }
    }
}
