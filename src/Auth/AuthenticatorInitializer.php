<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class AuthenticatorInitializer implements Initializer
{
    public function initialize(Container $container): Authenticator
    {
        $authConfig = $container->get(AuthConfig::class);

        return $container->get($authConfig->authenticator);
    }
}
