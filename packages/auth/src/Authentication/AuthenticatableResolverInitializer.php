<?php

namespace Tempest\Auth\Authentication;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class AuthenticatableResolverInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AuthenticatableResolver
    {
        return new DatabaseAuthenticatableResolver();
    }
}
