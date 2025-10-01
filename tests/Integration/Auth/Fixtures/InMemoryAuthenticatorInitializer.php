<?php

namespace Tests\Tempest\Integration\Auth\Fixtures;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class InMemoryAuthenticatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Authenticator
    {
        return new InMemoryAuthenticator();
    }
}
