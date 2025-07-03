<?php

namespace Tests\Tempest\Integration\Cryptography;

use Tempest\Cryptography\Signing\Signer;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class SignerTest extends FrameworkIntegrationTestCase
{
    private Signer $signer {
        get => $this->container->get(Signer::class);
    }

    public function test_default_algorithm(): void
    {
        $this->assertSame(SigningAlgorithm::SHA256, $this->signer->algorithm);
    }

    public function test_signature_valid(): void
    {
        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $data = 'important data';
        $signature = $this->signer->sign($data);

        $this->assertTrue($this->signer->verify($data, $signature));
    }

    public function test_update_key(): void
    {
        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $signature = $this->signer->sign('important data');
        $this->assertTrue($this->signer->verify('important data', $signature));

        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key2',
        ));

        $this->container->unregister(Signer::class);

        $this->assertFalse($this->signer->verify('important data', $signature));
    }
}
