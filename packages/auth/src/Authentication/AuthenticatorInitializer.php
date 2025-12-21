<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;

final readonly class AuthenticatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Authenticator
    {
        return new SessionAuthenticator(
            sessionManager: $container->get(SessionManager::class),
            session: $container->get(Session::class),
            authenticatableResolver: $container->get(AuthenticatableResolver::class),
        );
    }
}
