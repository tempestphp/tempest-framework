<?php

use Tempest\Core\ExceptionProcessor;

final class MyExceptionLogger implements ExceptionProcessor
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function process(\Throwable $exception): void
    {
        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
        ]);
    }
}
