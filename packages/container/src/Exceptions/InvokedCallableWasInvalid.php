<?php

declare(strict_types=1);

namespace Tempest\Container\Exceptions;

use Exception;
use Tempest\Container\Dependency;

final class InvokedCallableWasInvalid extends Exception implements ContainerException
{
    public function __construct(Dependency $dependency)
    {
        parent::__construct("[{$dependency->getName()}] cannot be invoked through the container.");
    }
}
