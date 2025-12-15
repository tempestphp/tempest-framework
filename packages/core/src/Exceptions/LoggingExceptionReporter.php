<?php

namespace Tempest\Core\Exceptions;

use Tempest\Core\ProvidesContext;
use Tempest\Debug\Debug;
use Tempest\Log\Logger;
use Throwable;

/**
 * An exception reporter that write exceptions through the {@see Logger}.
 */
final class LoggingExceptionReporter implements ExceptionReporter
{
    public function __construct(
        private readonly Logger $logger,
    ) {}

    public function report(Throwable $throwable): void
    {
        $items = [
            'class' => $throwable::class,
            'exception' => $throwable->getMessage(),
            'trace' => $throwable->getTraceAsString(),
            'context' => $throwable instanceof ProvidesContext
                ? $throwable->context()
                : [],
        ];

        $this->logger->error($throwable->getMessage(), $items);

        Debug::resolve()->log($items, writeToOut: false);
    }
}
