<?php

declare(strict_types=1);

namespace Tempest\Clock;

interface Timebox
{

    /**
     * Run callback at least for the given number of microseconds.
     *
     * @template TCallbackReturnType
     *
     * @param  callable(): TCallbackReturnType  $callable
     * @param  int  $microseconds
     * @return TCallbackReturnType
     */
    public function run(callable $callable, int $microseconds): mixed;

}
