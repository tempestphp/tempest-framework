<?php

namespace Tempest\Core;

use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Router\Exceptions\HttpExceptionHandler;

final class ExceptionHandlerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ExceptionHandler
    {
        $config = $container->get(AppConfig::class);

        return match (true) {
            PHP_SAPI === 'cli' => $container->get(ConsoleExceptionHandler::class),
            $config->environment->isLocal() => $container->get(DevelopmentExceptionHandler::class),
            default => $container->get(HttpExceptionHandler::class),
        };
    }
}
