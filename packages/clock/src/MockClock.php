<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class MockClock implements Clock
{
    private DateTimeInterface $now;

    public function __construct(DateTimeImmutable|DateTimeInterface|string $now = 'now')
    {
        $this->setNow($now);
    }

    public function toPsrClock(): ClockInterface
    {
        return new PsrClock($this);
    }

    public function now(): DateTimeInterface
    {
        return $this->now;
    }

    /**
     * Globally sets the current time to the specified value.
     */
    public function setNow(DateTimeImmutable|DateTimeInterface|string $now): void
    {
        $this->now = DateTime::parse($now);
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
            $this->plus($milliseconds);
            return;
        }

        $this->now = $this->now->plusMilliseconds($milliseconds);
    }

    /**
     * Adds the given duration. Providing an integer value adds the corresponding seconds to the current time.
     */
    public function plus(int|Duration $duration): void
    {
        if (is_int($duration)) {
            $duration = Duration::seconds($duration);
        }

        $this->now = $this->now->plus($duration);
    }

    /**
     * Removes the given duration. Providing an integer value removes the corresponding seconds to the current time.
     */
    public function minus(int|Duration $duration): void
    {
        if (is_int($duration)) {
            $duration = Duration::seconds($duration);
        }

        $this->now = $this->now->minus($duration);
    }

    public function dd(): void
    {
        // @phpstan-ignore disallowed.function
        dd($this->now); // @mago-expect best-practices/no-debug-symbols
    }
}
