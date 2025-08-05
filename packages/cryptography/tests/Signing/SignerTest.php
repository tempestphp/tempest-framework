<?php

namespace Tempest\Cryptography\Tests\Signing;

use PHPUnit\Framework\ExpectationFailedException;
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

    public function test_good_signature(): void
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

    public function test_bad_signature(): void
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

    public function test_different_algoritms(): void
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

    public function test_no_signing_key(): void
    {
        $this->expectException(SigningKeyWasInvalid::class);

        $signer = $this->createSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: '',
            minimumExecutionDuration: false,
        ));

        $signer->sign('important data');
    }

    public function test_empty_data(): void
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

    public function test_consistent_signature(): void
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

    public function test_different_keys(): void
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

    public function test_time_protection(): void
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

        $this->assertEqualsToMoreOrLess(0.3, $elapsed, margin: 0.015);
    }

    public function test_time_protection_with_mock_clock(): void
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
