<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class ControllerMethodHadNoRoute extends Exception implements RouterException
{
    public function __construct(string $controllerClass, string $controllerMethod)
    {
        parent::__construct("No route found for `{$controllerClass}::{$controllerMethod}()`. Did you forget to add a `Route` attribute?");
    }
}
