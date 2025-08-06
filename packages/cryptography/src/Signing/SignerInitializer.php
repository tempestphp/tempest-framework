<?php

namespace Tempest\Cryptography\Signing;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Cryptography\Timelock;

final class SignerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Signer
    {
        return new GenericSigner(
            config: $container->get(SigningConfig::class),
            timelock: $container->get(Timelock::class),
        );
    }
}
