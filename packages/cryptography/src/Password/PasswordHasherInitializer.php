<?php

namespace Tempest\Cryptography\Password;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class PasswordHasherInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): PasswordHasher
    {
        return new GenericPasswordHasher($container->get(PasswordHashingConfig::class));
    }
}
