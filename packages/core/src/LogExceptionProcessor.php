<?php

namespace Tempest\Core;

use Tempest\Debug\Debug;
use Tempest\Log\Logger;
use Throwable;

/**
 * An exception processor that logs exceptions.
 */
final class LogExceptionProcessor implements ExceptionProcessor
{
    public function __construct(
        private readonly Logger $logger,
    ) {}

    public function process(Throwable $throwable): void
    {
        $items = [
            'class' => $throwable::class,
            'exception' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
            'context' => $throwable instanceof HasContext
                ? $throwable->context()
                : [],
        ];

        $this->logger->error($throwable->getMessage(), $items);

        Debug::resolve()->log($items, writeToOut: false);
    }
}
