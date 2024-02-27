<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use ReflectionParameter;
use Tempest\Container\ContainerLog;

final class CannotAutowireException extends Exception
{
    public function __construct(ContainerLog $log, ReflectionParameter $origin)
    {
        $message = PHP_EOL. PHP_EOL;

        $message .= sprintf(
            '$%s in %s::__construct().',
            $origin->getName(),
            $origin->getDeclaringClass()->getName(),
        );

        $message .= $log;

        $message .= PHP_EOL;

        parent::__construct(
            message: $message,
        );
    }
}
