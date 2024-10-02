<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Cookie\SetCookieMiddleware;

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
