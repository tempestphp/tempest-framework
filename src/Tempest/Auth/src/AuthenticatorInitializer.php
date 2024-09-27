<?php

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class AuthenticatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Authenticator
    {
        $authConfig = $container->get(AuthConfig::class);

        return $container->get($authConfig->authenticatorClass);
    }
}