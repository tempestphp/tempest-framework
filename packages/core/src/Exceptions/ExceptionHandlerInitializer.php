<?php

namespace Tempest\Core\Exceptions;

use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\ExceptionHandler;
use Tempest\Router\Exceptions\HttpExceptionHandler;

final class ExceptionHandlerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ExceptionHandler
    {
        return match (true) {
            PHP_SAPI === 'cli' => $container->get(ConsoleExceptionHandler::class),
            default => $container->get(HttpExceptionHandler::class),
        };
    }
}
