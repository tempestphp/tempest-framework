<?php

namespace Tempest\Core\Exceptions;

use Tempest\Container\Container;
use Throwable;

/**
 * Reports exceptions to registered exception processors.
 */
final class GenericExceptionProcessor implements ExceptionProcessor
{
    public function __construct(
        private readonly ExceptionsConfig $config,
        private readonly Container $container,
    ) {}

    public function process(Throwable $throwable): void
    {
        foreach ($this->config->reporters as $reporter) {
            try {
                $handler = $this->container->get($reporter);
                $handler->report($throwable);
            } catch (Throwable) {
                // @mago-expect lint:no-empty-catch-clause
                // If something went wrong with the exception reporter,
                // we silently ignore it to avoid infinite loops.
            }
        }
    }
}
