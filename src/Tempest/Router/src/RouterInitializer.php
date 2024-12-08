<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Router\Cookie\SetCookieMiddleware;

final readonly class RouterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Router
    {
        $router = $container->get(GenericRouter::class);

        $router->addMiddleware(SetCookieMiddleware::class);

        return $router;
    }
}
