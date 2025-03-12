<?php

namespace Tempest\Router\Input;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class PostInputStreamInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): PostInputStream
    {
        return new StdinPostInputStream();
    }
}
