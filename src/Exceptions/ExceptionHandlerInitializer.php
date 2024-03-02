<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ExceptionHandlerInitializer implements Initializer
{
    public function initialize(Container $container): ExceptionHandler
    {
        $application = $container->get(Application::class);

        return match($application::class) {
            HttpApplication::class => $container->get(HttpExceptionHandler::class),
            ConsoleApplication::class => $container->get(ConsoleExceptionHandler::class),
        };
    }
}
