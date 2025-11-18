<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;
use Tempest\Support\Arr;

final class ControllerMethodHasMultipleRoutes extends Exception implements RouterException
{
    /** @param string[] $routes */
    public function __construct(string $controllerClass, string $controllerMethod, array $routes)
    {
        parent::__construct(vsprintf(
            format: "Controller method `%s::%s()` has multiple different routes: \"%s\". Please use the route path directly.",
            values: [
                $controllerClass,
                $controllerMethod,
                Arr\join($routes),
            ],
        ));
    }
}
