<?php

namespace Tempest\Cryptography\Encryption;

interface Encrypter
{
    public EncryptionAlgorithm $algorithm {
        get;
    }

    /**
     * Encrypts the specified data.
     */
    public function encrypt(#[\SensitiveParameter] string $data): EncryptedData;

    /**
     * Decrypts the specified data.
     */
    public function decrypt(string|EncryptedData $data): string;
}
