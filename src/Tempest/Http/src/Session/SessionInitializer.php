<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class SessionInitializer implements Initializer
{
    public function initialize(Container $container): Session
    {
        $sessionManager = $container->get(SessionManager::class);

        $sessionIdResolver = $container->get(SessionIdResolver::class);

        return $sessionManager->create($sessionIdResolver->resolve());
    }
}
