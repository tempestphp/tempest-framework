<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class ControllerSentNoOutput extends Exception implements RouterException
{
    public function __construct(string $controllerClass, string $controllerMethod)
    {
        parent::__construct("{$controllerClass}::{$controllerMethod}() did not return anything");
    }
}
