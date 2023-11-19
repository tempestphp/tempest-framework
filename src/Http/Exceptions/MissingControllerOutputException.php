<?php

declare(strict_types=1);

namespace Tempest\Http\Exceptions;

use Exception;

final class MissingControllerOutputException extends Exception
{
    public function __construct(string $controllerClass, string $controllerMethod)
    {
        parent::__construct("{$controllerClass}::{$controllerMethod}() did not return anything");
    }
}
