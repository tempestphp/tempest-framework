<?php

namespace Tempest\Cryptography\Signing;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class SignerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Signer
    {
        return new GenericSigner($container->get(SigningConfig::class));
    }
}
