<?php

namespace Tempest\Cryptography\Tests\Encryption;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionKeyWasInvalid;
use Tempest\Cryptography\Encryption\Exceptions\SignatureMismatched;
use Tempest\Cryptography\Encryption\GenericEncrypter;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\Cryptography\Tests\CreatesEncrypter;
use Tempest\Cryptography\Tests\CreatesSigner;
use Tempest\Cryptography\Tests\HasMoreIntegerAssertions;
use Tempest\DateTime\Duration;

final class EncryptionTest extends TestCase
{
    use CreatesEncrypter;
    use HasMoreIntegerAssertions;

    #[TestWith([''])]
    #[TestWith(['sensitive data'])]
    #[TestWith(['{"foo":"bar"}'])]
    public function test_encrypt(string $data): void
    {
        $encrypter = $this->createEncrypter();
        $encrypted = $encrypter->encrypt($data);

        $serialized = $encrypted->serialize();

        $this->assertTrue(json_validate(base64_decode($serialized, strict: true)));
        $this->assertSame($data, $encrypter->decrypt($serialized));
    }

    public function test_time_protection(): void
    {
        $encrypter = $this->createEncrypter(minimumExecutionDuration: Duration::milliseconds(300));

        $encrypted = $encrypter->encrypt('important data');

        $start = microtime(true);
        $this->assertSame('important data', $encrypter->decrypt($encrypted));
        $elapsed = microtime(true) - $start;

        $this->assertEqualsToMoreOrLess(0.3, $elapsed, margin: 0.020, windowsMargin: 0.025);
    }

    public function test_wrong_key(): void
    {
        $this->expectException(SignatureMismatched::class);

        $encrypter = $this->createEncrypter(EncryptionKey::generate(EncryptionAlgorithm::AES_256_GCM));
        $encrypted = $encrypter->encrypt('sensitive data');

        $wrongEncrypter = $this->createEncrypter(EncryptionKey::generate(EncryptionAlgorithm::AES_256_GCM));
        $wrongEncrypter->decrypt($encrypted->serialize());
    }

    public function test_missing_key(): void
    {
        $this->expectException(EncryptionKeyWasInvalid::class);

        $encrypter = $this->createEncrypter(key: '');
        $encrypter->encrypt('sensitive data');
    }
}
