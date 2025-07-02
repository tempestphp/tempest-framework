<?php

namespace Tempest\Core;

use Tempest\Debug\Debug;
use Throwable;

/**
 * An exception processor that logs exceptions.
 */
final class LogExceptionProcessor implements ExceptionProcessor
{
    public function process(Throwable $throwable): void
    {
        $items = [
            'exception' => $throwable->getMessage(),
            'context' => ($throwable instanceof HasContext)
                ? $throwable->context()
                : [],
        ];

        Debug::resolve()->log($items, writeToOut: false);
    }
}
