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

    public function changeTime(int $seconds): void
    {
        if ($seconds < 0) {
            $seconds = abs($seconds);
            $this->now = $this->now->sub(new DateInterval("PT{$seconds}S"));
        } else {
            $this->now = $this->now->add(new DateInterval("PT{$seconds}S"));
        }
    }
}
