<?php

namespace Tempest\Cryptography;

use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;

final class Timelock
{
    public function __construct(
        private readonly Clock $clock,
    ) {}

    /**
     * Whether or not a time-locked operation can return early.
     */
    public bool $canReturnEarly = false;

    /**
     * Performs an operation that is time-locked to a specific duration.
     *
     * @template TCallReturnType
     *
     * @param (callable($this): TCallReturnType) $callback
     * @param Duration $duration
     * @return TCallReturnType
     */
    public function invoke(callable $callback, Duration $duration): mixed
    {
        $exception = null;
        $start = microtime(as_float: true);

        try {
            $result = $callback($this);
        } catch (\Throwable $thrown) {
            $exception = $thrown;
        }

        $remainderInMicroseconds = intval($duration->getTotalMicroseconds() - ((microtime(true) - $start) * 1_000_000));

        if (! $this->canReturnEarly && $remainderInMicroseconds > 0) {
            $this->clock->sleep(Duration::microseconds($remainderInMicroseconds));
        }

        if ($exception) {
            throw $exception;
        }

        return $result;
    }
}
