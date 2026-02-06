<?php

namespace App\Exceptions;

use ExceptionProcessor;

final class SimpleExceptionProcessor implements ExceptionProcessor
{
    public function process(\Throwable $exception): void
    {
        echo $exception->getMessage();
    }
}
