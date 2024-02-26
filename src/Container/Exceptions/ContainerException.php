<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Tempest\Container\ContainerLog;
use Throwable;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    public function __construct(ContainerLog $log, Throwable $previous)
    {
        parent::__construct(
            message: "Could not resolve {$log}",
            previous: $previous,
        );
    }
}
