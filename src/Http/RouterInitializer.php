<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class RouterInitializer implements Initializer
{
    public function initialize(Container $container): Router
    {
        return $container->get(GenericRouter::class);
    }
}
