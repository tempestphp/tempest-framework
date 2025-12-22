<?php

namespace Tempest\Core\Exceptions;

use Throwable;

/**
 * Classes implementing this interface are automatically discovered and may report all thrown exceptions in some way.
 */
interface ExceptionReporter
{
    /**
     * Reports the given exception.
     */
    public function report(Throwable $throwable): void;
}
