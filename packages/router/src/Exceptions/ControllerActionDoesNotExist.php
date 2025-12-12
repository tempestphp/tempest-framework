<?php

declare(strict_types=1);

namespace Tempest\Router\Exceptions;

use Exception;

final class ControllerActionDoesNotExist extends Exception implements RouterException
{
    public static function controllerNotFound(string $controller): self
    {
        return new self("The controller class `{$controller}` does not exist.");
    }

    public static function actionNotFound(string $controller, string $method): self
    {
        return new self("The method `{$method}()` does not exist in controller class `{$controller}`.");
    }
}
