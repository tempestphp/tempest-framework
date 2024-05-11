<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
    public function now(): DateTimeImmutable;

    public function time(TimeUnit $unit = TimeUnit::SECOND): int;

    public function sleep(int $time, TimeUnit $unit = TimeUnit::SECOND): void;
}
