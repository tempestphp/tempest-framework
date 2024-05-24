<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton(tag: 'database')]
final readonly class DatabaseCredentialsInitializer implements Initializer
{
    public function initialize(Container $container): CredentialsResolver
    {
        $authConfig = $container->get(AuthConfig::class);

        return $container->get($authConfig->credentials['database']);
    }
}
