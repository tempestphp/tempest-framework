<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Request;
use Tempest\Http\Session\Resolvers\HeaderSessionIdResolver;

#[Singleton]
final readonly class SessionInitializer implements Initializer
{
    public function initialize(Container $container): Session
    {
        $sessionManager = $container->get(SessionManager::class);

        // TODO: support configurable resolvers
        $id = (new HeaderSessionIdResolver($container->get(Request::class)))->resolve();

        return $sessionManager->create($id);
    }
}
