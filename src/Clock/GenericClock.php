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

    public function time(): int
    {
        return time();
    }

    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    public function utime(): int
    {
        return (int) (microtime(true) * 1_000_000);
    }

    public function usleep(int $microseconds): void
    {
        usleep($microseconds);
    }
}
