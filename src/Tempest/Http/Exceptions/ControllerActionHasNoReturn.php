<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Exception;
use Tempest\Http\Route;

final class ControllerActionHasNoReturn extends Exception
{
    public function __construct(Route $route)
    {
        parent::__construct(sprintf(
            'The controller action %s::%s doesn\'t return a valid response',
            $route->handler->getDeclaringClass()->getName(),
            $route->handler->getName(),
        ));
    }
}
