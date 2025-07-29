<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Core\AppConfig;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Request;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;
use Tempest\Http\Session\SessionConfig;

final readonly class SessionIdResolverInitializer implements Initializer
{
    public function initialize(Container $container): SessionIdResolver
    {
        return new CookieSessionIdResolver(
            appConfig: $container->get(AppConfig::class),
            request: $container->get(Request::class),
            cookies: $container->get(CookieManager::class),
            sessionConfig: $container->get(SessionConfig::class),
            clock: $container->get(Clock::class),
        );
    }
}
