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
            'class' => $throwable::class,
            'exception' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
            'context' => ($throwable instanceof HasContext)
                ? $throwable->context()
                : [],
        ];

        Debug::resolve()->log($items, writeToOut: false);
    }
}
