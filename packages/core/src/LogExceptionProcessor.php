<?php

namespace Tempest\Core;

use Tempest\Debug\Debug;
use Throwable;

/**
 * An exception processor that logs exceptions.
 */
final class LogExceptionProcessor implements ExceptionProcessor
{
    public function process(Throwable $throwable): Throwable
    {
        $items = [
            'class' => $throwable::class,
            'exception' => $throwable->getMessage(),
            'context' => ($throwable instanceof HasContext)
                ? $throwable->context()
                : [],
        ];

        Debug::resolve()->log($items, writeToOut: false);

        return $throwable;
    }
}
