<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\ContainerLog;

final class CircularDependencyException extends Exception
{
    public function __construct(string $className, ContainerLog $log)
    {
        parent::__construct("Circular dependency on {$className}{$log}");
    }
}
