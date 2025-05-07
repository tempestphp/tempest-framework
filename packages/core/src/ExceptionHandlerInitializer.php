<?php

namespace Tempest\Core;

use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Router\Exceptions\HttpExceptionHandler;

final class ExceptionHandlerInitializer implements Initializer
{
    public function initialize(Container $container): ExceptionHandler
    {
        if (PHP_SAPI === 'cli') {
            return $container->get(ConsoleExceptionHandler::class);
        }

        return $container->get(HttpExceptionHandler::class);
    }
}
