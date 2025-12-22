<?php

namespace Tempest\Core\Exceptions;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class ExceptionProcessorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ExceptionProcessor
    {
        return new GenericExceptionProcessor(
            config: $container->get(ExceptionsConfig::class),
            container: $container,
        );
    }
}
