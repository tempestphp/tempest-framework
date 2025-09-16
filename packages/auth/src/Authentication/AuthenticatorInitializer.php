<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Auth\AuthConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Session\Session;

final readonly class AuthenticatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Authenticator
    {
        return new SessionAuthenticator(
            authConfig: $container->get(AuthConfig::class),
            session: $container->get(Session::class),
            authenticatableResolver: $container->get(AuthenticatableResolver::class),
        );
    }
}
