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
     * Reports the given exception to the registered exception processors.
     */
    public function report(Throwable $throwable): void
    {
        $this->reported[] = $throwable;

        if (! $this->enabled) {
            return;
        }

        /** @var class-string<\Tempest\Core\ExceptionProcessor> $processor */
        foreach ($this->appConfig->exceptionProcessors as $processor) {
            $handler = $this->container->get($processor);
            $handler->process($throwable);
        }
    }
}
