<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use ReflectionClass;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;

final readonly class OpaqueSessionInitializer implements Initializer
{
    public function initialize(Container $container): OpaqueSession
    {
        $sessionIdResolverReflector = new ReflectionClass(CookieSessionIdResolver::class);
        // sessionIdResolver depends on Request, which is not available at the time of initialization, so we create a lazy proxy for it
        // TODO: maybe it should take request as a parameter, not a dependency
        $sessionIdResolver = $sessionIdResolverReflector->newLazyProxy(fn () => $container->get(SessionIdResolver::class));

        return new OpaqueSession(
            sessionIdResolver: $sessionIdResolver,
            sessionManager: $container->get(SessionManager::class),
        );
    }
}
