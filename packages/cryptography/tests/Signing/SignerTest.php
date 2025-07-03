<?php

namespace Tempest\Cryptography\Tests\Signing;

use PHPUnit\Framework\TestCase;
use Tempest\Cryptography\Signing\Exceptions\SigningKeyWasMissing;
use Tempest\Cryptography\Signing\GenericSigner;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;

final class SignerTest extends TestCase
{
    public function test_good_signature(): void
    {
        $signer = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $data = 'important data';
        $signature = $signer->sign($data);

        $this->assertTrue($signer->verify($data, $signature));
    }

    public function test_bad_signature(): void
    {
        $signer = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $data = 'important data';
        $signature = $signer->sign($data);

        // Tamper with the data
        $tamperedData = 'tampered data';

        $this->assertFalse($signer->verify($tamperedData, $signature));
    }

    public function test_different_algoritms(): void
    {
        $signer1 = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $signer2 = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA512,
            key: 'my_secret_key',
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
        $this->expectException(SigningKeyWasMissing::class);

        $signer = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: '',
        ));

        $signer->sign('important data');
    }

    public function test_empty_data(): void
    {
        $signer = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $signature = $signer->sign('');

        // An empty string should still produce a valid signature
        $this->assertTrue($signer->verify('', $signature));
    }

    public function test_consistent_signature(): void
    {
        $signer = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'my_secret_key',
        ));

        $data = 'important data';
        $signature1 = $signer->sign($data);
        $signature2 = $signer->sign($data);

        // Signing the same data should produce the same signature
        $this->assertEquals($signature1, $signature2);
    }

    public function test_different_keys(): void
    {
        $signer1 = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA256,
            key: 'signer1_key_foo',
        ));

        $signer2 = new GenericSigner(new SigningConfig(
            algorithm: SigningAlgorithm::SHA512,
            key: 'signer2_key_bar',
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
}
