<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class ControllerMethodHadNoRouteAttribute extends Exception implements RouterException
{
    public function __construct(string $controllerClass, string $controllerMethod)
    {
        parent::__construct("No route found {$controllerClass}::{$controllerMethod}() did you add a Route attribute?");
    }
}
