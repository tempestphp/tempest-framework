<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Exception;

final class InvalidRouteException extends Exception
{
    public function __construct(string $controllerClass, string $controllerMethod)
    {
        parent::__construct("No route found {$controllerClass}::{$controllerMethod}() did you add a Route attribute?");
    }
}
