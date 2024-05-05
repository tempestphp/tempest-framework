<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Throwable;

final class GenericTimebox implements Timebox
{

    public function __construct(
        protected Clock $clock,
    ) {}

    public function run(callable $callable, int $microseconds): mixed
    {
        $throwable = null;

        $start = $this->clock->utime();

        try {
            $result = $callable();
        } catch (Throwable $exception) {
            $throwable = $exception;
        }

        $remaining = $microseconds - ($this->clock->utime() - $start);

        $this->clock->usleep($remaining);

        if ($throwable) {
            throw $throwable;
        }

        return $result ?? null;
    }
}
