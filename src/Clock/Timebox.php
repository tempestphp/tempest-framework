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

    public function run(callable $callable, int $microseconds, bool $returnEarly = false): mixed
    {
        $throwable = null;
        $result = null;

        $start = $this->clock->utime();

        try {
            $result = $callable();
        } catch (Throwable $exception) {
            $throwable = $exception;
        }

        if ($returnEarly && $throwable === null) {
            return $result;
        }

        $remaining = $microseconds - ($this->clock->utime() - $start);

        $this->clock->usleep($remaining);

        if ($throwable) {
            throw $throwable;
        }

        return $result;
    }
}
