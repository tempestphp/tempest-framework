<?php

namespace Tests\Tempest\Integration\Cryptography;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Cryptography\Signing\Signer;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class SignerTest extends FrameworkIntegrationTestCase
{
    private Signer $signer {
        get => $this->container->get(Signer::class);
    }

    #[Test]
    public function default_algorithm(): void
    {
        $this->assertSame(SigningAlgorithm::SHA256, $this->signer->algorithm);
    }

    #[Test]
    public function signature_valid(): void
    {
        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature = $this->signer->sign($data);

        $this->assertTrue($this->signer->verify($data, $signature));
    }

    #[Test]
    public function update_key(): void
    {
        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $signature = $this->signer->sign('important data');
        $this->assertTrue($this->signer->verify('important data', $signature));

        $this->container->config(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key2',
            minimumExecutionDuration: false,
        ));

        $this->container->unregister(Signer::class);

        $this->assertFalse($this->signer->verify('important data', $signature));
    }
}
