<?php

namespace Tempest\Cryptography\Encryption;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Cryptography\Signing\Signer;

final class EncrypterInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Encrypter
    {
        return new GenericEncrypter(
            signer: $container->get(Signer::class),
            config: $container->get(EncryptionConfig::class),
        );
    }
}
