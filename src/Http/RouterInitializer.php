<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class RouterInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $router = $container->get(GenericRouter::class);

        $container->singleton(Router::class, fn () => $router);

        return $router;
    }
}
