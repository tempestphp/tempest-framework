<?php

namespace Tempest\Clock;

use DateTimeImmutable;
use DateTimeInterface;

final class GenericClock implements Clock
{
    public function now(): DateTimeInterface
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
}