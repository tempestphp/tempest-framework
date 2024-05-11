<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Throwable;

final readonly class Timebox
{
    public function __construct(
        protected Clock $clock,
    ) {
    }

    public function run(callable $callable, int $time, TimeUnit $unit = TimeUnit::SECOND, bool $returnEarly = false): mixed
    {
        $throwable = null;
        $result = null;

        $start = $this->clock->time(unit: TimeUnit::MICROSECOND);

        try {
            $result = $callable();
        } catch (Throwable $exception) {
            $throwable = $exception;
        }

        if ($returnEarly && $throwable === null) {
            return $result;
        }

        $remaining = ($time * $unit->toMicroseconds()) - ($this->clock->time(unit: TimeUnit::MICROSECOND) - $start);

        $this->clock->sleep($remaining, unit: TimeUnit::MICROSECOND);

        if ($throwable) {
            throw $throwable;
        }

        return $result;
    }
}
