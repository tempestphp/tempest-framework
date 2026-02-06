<?php

use Tempest\Core\ExceptionProcessor;

final class MyCustomExceptionProcessor implements ExceptionProcessor
{
    public function process(\Throwable $exception): void
    {
        log_error($exception->getMessage());
    }
}
