<?php

namespace Tempest\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class PsrClock implements ClockInterface
{
    public function __construct(
        private Clock $clock,
    ) {}

    public function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromTimestamp($this->clock->timestamp());
    }
}
