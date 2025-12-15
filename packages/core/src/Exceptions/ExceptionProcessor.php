<?php

namespace Tempest\Core\Exceptions;

use Throwable;

/**
 * Responsible for processing exceptions when they are thrown.
 * By default, Tempest implements {@see GenericExceptionProcessor}, which calls all discovered {@see ExceptionReporter}.
 */
interface ExceptionProcessor
{
    /**
     * Processes the given exception.
     */
    public function process(Throwable $throwable): void;
}
