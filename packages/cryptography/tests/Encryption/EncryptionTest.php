<?php

namespace Tempest\Cryptography\Tests\Encryption;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Cryptography\Encryption\EncryptedData;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionConfig;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Cryptography\Encryption\Exceptions\DecryptionFailed;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionKeyWasInvalid;
use Tempest\Cryptography\Encryption\Exceptions\SignatureMismatched;
use Tempest\Cryptography\Encryption\GenericEncrypter;
use Tempest\Cryptography\Signing\SigningAlgorithm;
use Tempest\Cryptography\Signing\SigningConfig;
use Tempest\Cryptography\Tests\CreatesSigner;
use Tempest\DateTime\Duration;

final class EncryptionTest extends TestCase
{
    use CreatesSigner;

    private function createEncrypter(?string $key = null, false|Duration $minimumExecutionDuration = false): GenericEncrypter
    {
        $key ??= EncryptionKey::generate(EncryptionAlgorithm::AES_256_GCM)->value;

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

    #[TestWith([''])]
    #[TestWith(['sensitive data'])]
    #[TestWith(['{"foo":"bar"}'])]
    public function test_encrypt(string $data): void
    {
        $encrypter = $this->createEncrypter();
        $encrypted = $encrypter->encrypt($data);

        $serialized = $encrypted->serialize();

        $this->assertTrue(json_validate(base64_decode($serialized)));
        $this->assertSame($data, $encrypter->decrypt($serialized));
    }

    public function test_time_protection(): void
    {
        $encrypter = $this->createEncrypter(minimumExecutionDuration: Duration::milliseconds(300));

        $encrypted = $encrypter->encrypt('important data');

        $start = microtime(true);
        $this->assertSame('important data', $encrypter->decrypt($encrypted));
        $elapsed = microtime(true) - $start;

        $this->assertGreaterThanOrEqual(0.29, $elapsed);
        $this->assertLessThanOrEqual(0.311, $elapsed);
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
