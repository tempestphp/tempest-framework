<?php

namespace Tempest\Router\Input;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class InputStreamInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): InputStream
    {
        return new StdinInputStream();
    }
}
