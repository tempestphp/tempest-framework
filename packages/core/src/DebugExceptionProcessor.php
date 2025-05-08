<?php

namespace Tempest\Core;

use Tempest\Debug\Debug;
use Throwable;

final class DebugExceptionProcessor implements ExceptionProcessor
{
    public function process(Throwable $throwable): Throwable
    {
        $items = [
            'exception' => $throwable->getMessage(),
            'context' => ($throwable instanceof HasContext)
                ? $throwable->context()
                : [],
        ];

        Debug::resolve()->log($items, writeToOut: false);

        return $throwable;
    }
}
