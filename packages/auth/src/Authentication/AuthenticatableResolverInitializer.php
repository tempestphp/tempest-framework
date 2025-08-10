<?php

namespace Tempest\Auth\Authentication;

use Tempest\Auth\AuthConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Database\Database;

final class AuthenticatableResolverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AuthenticatableResolver
    {
        return new DatabaseAuthenticatableResolver(
            authConfig: $container->get(AuthConfig::class),
            database: $container->get(Database::class),
        );
    }
}
