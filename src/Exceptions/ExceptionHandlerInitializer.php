<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ExceptionHandlerInitializer implements Initializer
{
    public function initialize(Container $container): ExceptionHandler
    {
        $application = $container->get(Application::class);

        $exceptionHandler = match($application::class) {
            HttpApplication::class => $container->get(HttpExceptionHandler::class),
            ConsoleApplication::class => $container->get(ConsoleExceptionHandler::class),
        };

        $container->singleton(ExceptionHandler::class, fn () => $exceptionHandler);

        return $exceptionHandler;
    }
}
