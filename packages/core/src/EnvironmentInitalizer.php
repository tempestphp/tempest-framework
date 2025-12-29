<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class EnvironmentInitalizer implements Initializer
{
    public function initialize(Container $container): Environment
    {
        return $container->get(AppConfig::class)->environment;
    }
}
