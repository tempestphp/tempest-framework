<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class RouterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Router
    {
        return $container->get(GenericRouter::class);
    }
}
