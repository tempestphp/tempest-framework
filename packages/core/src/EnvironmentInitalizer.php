<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class EnvironmentInitalizer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Environment
    {
        return Environment::guessFromEnvironment();
    }
}
