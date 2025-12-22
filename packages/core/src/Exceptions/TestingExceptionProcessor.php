<?php

namespace Tempest\Core\Exceptions;

use Throwable;

final class TestingExceptionProcessor implements ExceptionProcessor
{
    /**
     * @var array<Throwable> List of processed exceptions.
     */
    private(set) array $processed = [];

    public function __construct(
        private(set) ExceptionProcessor $processor,
        public bool $enabled,
    ) {}

    public function process(Throwable $throwable): void
    {
        $this->processed[] = $throwable;

        if ($this->enabled === false) {
            return;
        }

        $this->processor->process($throwable);
    }
}
