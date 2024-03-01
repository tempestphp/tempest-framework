<?php

namespace Tempest\Clock;

use DateInterval;
use DateTime;
use DateTimeInterface;

final class MockClock implements Clock
{
    private DateTimeInterface $now;

    public function __construct(DateTimeInterface|string $now = 'now')
    {
        $this->now = $now instanceof DateTimeInterface
            ? $now
            : new DateTime($now);
    }

    public function now(): DateTimeInterface
    {
        return $this->now;
    }

    public function time(): int
    {
        return $this->now->getTimestamp();
    }

    public function sleep(int $seconds): void
    {
        $this->now = $this->now->add(
            new DateInterval("PT{$seconds}S")
        );
    }
}