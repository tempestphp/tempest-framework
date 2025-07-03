<?php

namespace Tests\Tempest\Integration\Cryptography;

use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Cryptography\Signing\Signer;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class EncrypterTest extends FrameworkIntegrationTestCase
{
    private Encrypter $encrypter {
        get => $this->container->get(Encrypter::class);
    }

    public function test_default_algorithm(): void
    {
        $this->assertSame(EncryptionAlgorithm::AES_256_GCM, $this->encrypter->algorithm);
    }

    public function test_using_config(): void
    {
        $this->container->config(new EncryptionConfig(
            algorithm: EncryptionAlgorithm::AES_256_GCM,
            key: $key = EncryptionKey::generate(EncryptionAlgorithm::AES_256_GCM),
        ));

        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: $key,
            minimumExecutionDuration: false,
        ));

        $encrypted = $this->encrypter->encrypt('important data');

        $this->assertSame('important data', $this->encrypter->decrypt($encrypted));
    }
}
