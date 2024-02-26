<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Throwable;

final readonly class HttpExceptionHandler implements ExceptionHandler
{
    public function handle(Throwable $throwable): void
    {
        if (! function_exists('dd')) {
            // TODO
        }

        dd($throwable);
    }
}
