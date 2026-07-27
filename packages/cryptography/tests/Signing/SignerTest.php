<?php

namespace Tempest\Cryptography\Tests\Signing;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Clock\MockClock;
use Tempest\Cryptography\Signing\Exceptions\SigningKeyWasInvalid;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\Cryptography\Tests\CreatesSigner;
use Tempest\Cryptography\Tests\HasMoreIntegerAssertions;
use Tempest\DateTime\Duration;

final class SignerTest extends TestCase
{
    use CreatesSigner;
    use HasMoreIntegerAssertions;

    #[Test]
    public function good_signature(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature = $signer->sign($data);

        $this->assertTrue($signer->verify($data, $signature));
    }

    #[Test]
    public function bad_signature(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature = $signer->sign($data);

        // Tamper with the data
        $tamperedData = 'tampered data';

        $this->assertFalse($signer->verify($tamperedData, $signature));
    }

    #[Test]
    public function different_algoritms(): void
    {
        $signer1 = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $signer2 = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA512,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature1 = $signer1->sign($data);
        $signature2 = $signer2->sign($data);

        // Signatures should be different due to different algorithms
        $this->assertNotEquals($signature1, $signature2);

        // Verify with the correct signer
        $this->assertTrue($signer1->verify($data, $signature1));
        $this->assertTrue($signer2->verify($data, $signature2));

        // Verify with the wrong signer
        $this->assertFalse($signer1->verify($data, $signature2));
        $this->assertFalse($signer2->verify($data, $signature1));
    }

    #[Test]
    public function no_signing_key(): void
    {
        $this->expectException(SigningKeyWasInvalid::class);

        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: '', // @phpstan-ignore argument.type
            minimumExecutionDuration: false,
        ));

        $signed = $signer->sign('important data');
    }

    #[Test]
    public function empty_data(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $signature = $signer->sign('');

        // An empty string should still produce a valid signature
        $this->assertTrue($signer->verify('', $signature));
    }

    #[Test]
    public function consistent_signature(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature1 = $signer->sign($data);
        $signature2 = $signer->sign($data);

        // Signing the same data should produce the same signature
        $this->assertEquals($signature1, $signature2);
    }

    #[Test]
    public function different_keys(): void
    {
        $signer1 = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'signer1_key_foo',
            minimumExecutionDuration: false,
        ));

        $signer2 = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA512,
            key: 'signer2_key_bar',
            minimumExecutionDuration: false,
        ));

        $data = 'important data';
        $signature1 = $signer1->sign($data);
        $signature2 = $signer2->sign($data);

        // Signatures should be different due to different keys
        $this->assertNotEquals($signature1, $signature2);

        // Verify with the correct signer
        $this->assertTrue($signer1->verify($data, $signature1));
        $this->assertTrue($signer2->verify($data, $signature2));

        // Verify with the wrong signer
        $this->assertFalse($signer1->verify($data, $signature2));
        $this->assertFalse($signer2->verify($data, $signature1));
    }

    #[Test]
    public function time_protection(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: Duration::milliseconds(300),
        ));

        $data = 'important data';
        $signature = $signer->sign($data);

        $start = microtime(true);
        $this->assertTrue($signer->verify($data, $signature));
        $elapsed = microtime(true) - $start;

        $this->assertEqualsToMoreOrLess(0.3, $elapsed, margin: 0.015, windowsMargin: 0.025);
    }

    #[Test]
    public function time_protection_with_mock_clock(): void
    {
        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
            minimumExecutionDuration: Duration::second(),
        ), $clock = new MockClock());

        $data = 'important data';
        $signature = $signer->sign($data);

        $ms = $clock->timestamp()->getMilliseconds();
        $this->assertTrue($signer->verify($data, $signature));
        $elapsed = $clock->timestamp()->getMilliseconds() - $ms;

        $this->assertEqualsToMoreOrLess(1000, $elapsed, margin: 10);
    }
}
