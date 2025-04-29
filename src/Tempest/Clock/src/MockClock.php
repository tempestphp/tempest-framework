<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\DateTime\Timestamp;

final class MockClock implements Clock
{
    private DateTimeInterface $now;

    public function __construct(DateTimeImmutable|DateTimeInterface|string $now = 'now')
    {
        if ($now instanceof DateTimeImmutable) {
            $this->now = DateTime::fromTimestamp(
                Timestamp::fromParts($now->getTimestamp()),
            );
        } else {
            $this->now = DateTime::parse($now);
        }
    }

    public function toPsrClock(): ClockInterface
    {
        return new PsrClock($this);
    }

    public function now(): DateTimeInterface
    {
        return $this->now;
    }

    public function setNow(DateTimeInterface|string $now): void
    {
        if ($now instanceof DateTimeInterface) {
            $this->now = $now;
        } else {
            $this->now = DateTime::parse($now);
        }
    }

    public function timestamp(): int
    {
        return $this->now->getTimestamp()->getSeconds();
    }

    public function timestampMs(): int
    {
        return $this->now->getTimestamp()->getMilliseconds();
    }

    public function sleep(int|Duration $milliseconds): void
    {
        if ($milliseconds instanceof Duration) {
            $this->addInterval($milliseconds);
            return;
        }

        $this->now = $this->now->plusMilliseconds($milliseconds);
    }

    public function addInterval(Duration $duration): void
    {
        $this->now = $this->now->plus($duration);
    }

    public function subInternal(Duration $duration): void
    {
        $this->now = $this->now->minus($duration);
    }

    public function changeTime(int $seconds): void
    {
        if ($seconds < 0) {
            $seconds = abs($seconds);
            $this->now = $this->now->minusSeconds($seconds);
        } else {
            $this->now = $this->now->plusSeconds($seconds);
        }
    }

    public function dd(): void
    {
        // @phpstan-ignore disallowed.function
        dd($this->now); // @mago-expect best-practices/no-debug-symbols
    }
}
