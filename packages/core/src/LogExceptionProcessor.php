<?php

namespace Tempest\Core;

use Tempest\Log\Logger;
use Throwable;

use function Tempest\get;

/**
 * An exception processor that logs exceptions.
 */
final class LogExceptionProcessor implements ExceptionProcessor
{
    public function process(Throwable $throwable): Throwable
    {
        get(Logger::class)
            ?->error(
                $throwable->getMessage(),
                [
                    'exception' => $throwable,
                ],
            );

        return $throwable;
    }
}
