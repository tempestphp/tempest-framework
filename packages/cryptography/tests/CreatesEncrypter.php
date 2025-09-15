<?php

namespace Tempest\Cryptography\Tests;

use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Cryptography\Encryption\GenericEncrypter;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\DateTime\Duration;

trait CreatesEncrypter
{
    use CreatesSigner;

    private function createEncrypter(?string $key = null, false|Duration $minimumExecutionDuration = false): GenericEncrypter
    {
        $key ??= EncryptionKey::generate(EncryptionAlgorithm::AES_256_GCM)->toString();

        return new GenericEncrypter(
            signer: $this->createSigner(new SigningConfig(
                algorithm: SigningAlgorithm::SHA256,
                key: $key,
                minimumExecutionDuration: $minimumExecutionDuration,
            )),
            config: new EncryptionConfig(
                algorithm: EncryptionAlgorithm::AES_256_GCM,
                key: $key,
            ),
        );
    }
}
