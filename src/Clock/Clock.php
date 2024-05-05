<?php

declare(strict_types=1);

namespace Tempest\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
    public function now(): DateTimeImmutable;

    public function time(): int;

    public function sleep(int $seconds): void;

    public function utime(): int;

    public function usleep(int $microseconds): void;
}
