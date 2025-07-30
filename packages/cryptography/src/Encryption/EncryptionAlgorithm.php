<?php

namespace Tempest\Cryptography\Encryption;

enum EncryptionAlgorithm: string
{
    case AES_256_GCM = 'aes-256-gcm';
    case AES_256_CBC = 'aes-256-cbc';
    case AES_128_GCM = 'aes-128-gcm';
    case AES_128_CBC = 'aes-128-cbc';
    case CHACHA20_POLY1305 = 'chacha20-poly1305';

    /**
     * Returns the length of the key, in bytes, for the encryption algorithm.
     */
    public function getKeyLength(): int
    {
        return openssl_cipher_key_length($this->value);
    }

    /**
     * Returns the initialization vector (IV) length for the encryption algorithm.
     */
    public function getIvLength(): int
    {
        return openssl_cipher_iv_length($this->value);
    }

    /**
     * Determines if the encryption algorithm allows embedding associated data.
     *
     * @see https://en.wikipedia.org/wiki/Authenticated_encryption
     */
    public function isAead(): bool
    {
        return match ($this) {
            self::AES_256_GCM, self::AES_128_GCM, self::CHACHA20_POLY1305 => true,
            self::AES_256_CBC, self::AES_128_CBC => false,
        };
    }
}
