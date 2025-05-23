<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Throwable;

#[Singleton]
final class ExceptionReporter
{
    private(set) array $reported = [];

    public bool $enabled = true;

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly Container $container,
    ) {}

    /**
     * Reports the given exception to the registered exceptionm processors.
     */
    public function report(Throwable $throwable): void
    {
        $this->reported[] = $throwable;

        if (! $this->enabled) {
            return;
        }

        foreach ($this->appConfig->exceptionProcessors as $processor) {
            $handler = $this->container->get($processor);
            $throwable = $handler->process($throwable);
        }
    }
}
