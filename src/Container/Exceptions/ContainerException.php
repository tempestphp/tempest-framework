<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;
use Throwable;

final class ContainerException extends Exception
{
    public function __construct(ContainerLog $log, Throwable $previous)
    {
        parent::__construct(
            message: "Could not resolve {$log}",
            previous: $previous,
        );
    }
}
