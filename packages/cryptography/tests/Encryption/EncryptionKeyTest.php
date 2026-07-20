<?php

namespace Tempest\Cryptography\Tests\Encryption;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Cryptography\Encryption\EncryptionAlgorithm;
use Tempest\Cryptography\Encryption\EncryptionKey;
use Tempest\Cryptography\Encryption\Exceptions\EncryptionKeyWasInvalid;

final class EncryptionKeyTest extends TestCase
{
    #[Test]
    public function encryption_key(): void
    {
        $key = EncryptionKey::fromString('6+M/ai/szdyR+4NYJxbLhYGCdpSZPrdvZ51S83HLWrQ=', EncryptionAlgorithm::AES_256_GCM);

        $this->assertNotEmpty($key->value);
        $this->assertSame(strlen($key->value), $key->algorithm->getKeyLength());
        $this->assertSame(EncryptionAlgorithm::AES_256_GCM, $key->algorithm);
    }

    #[Test]
    public function encryption_key_with_null(): void
    {
        $this->expectException(EncryptionKeyWasInvalid::class);

        EncryptionKey::fromString(null, EncryptionAlgorithm::AES_256_GCM);
    }

    #[Test]
    public function encryption_key_with_empty_string(): void
    {
        $this->expectException(EncryptionKeyWasInvalid::class);

        EncryptionKey::fromString('', EncryptionAlgorithm::AES_256_GCM);
    }
}
