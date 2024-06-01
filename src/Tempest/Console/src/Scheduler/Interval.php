<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

final readonly class Interval
{
    public function __construct(
        public int $years = 0,
        public int $months = 0,
        public int $weeks = 0,
        public int $days = 0,
        public int $hours = 0,
        public int $minutes = 0,
        public int $seconds = 0,
    ) {

    }

    public function inSeconds(): int
    {
        return $this->years * 365 * 24 * 60 * 60
            + $this->months * 30 * 24 * 60 * 60
            + $this->weeks * 7 * 24 * 60 * 60
            + $this->days * 60 * 60 * 24
            + $this->hours * 60 * 60
            + $this->minutes * 60
            + $this->seconds;
    }
}
