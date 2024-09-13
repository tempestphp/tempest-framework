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
        return hrtime(true);
    }

    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }
}
