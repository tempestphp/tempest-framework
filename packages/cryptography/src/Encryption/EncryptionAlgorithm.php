<?php

namespace Tempest\Cryptography\Encryption;

enum EncryptionAlgorithm: string
{
    case AES_256_GCM = 'aes-256-gcm';
    case AES_256_CBC = 'aes-256-cbc';
    case AES_128_GCM = 'aes-128-gcm';
    case AES_128_CBC = 'aes-128-cbc';
    case CHACHA20_POLY1305 = 'chacha20-poly1305';

    public function getKeyLength(): int
    {
        return openssl_cipher_key_length($this->value);
    }

    public function getIvLength(): int
    {
        return openssl_cipher_iv_length($this->value);
    }

    public function isAead(): bool
    {
        return match ($this) {
            self::AES_256_GCM, self::AES_128_GCM, self::CHACHA20_POLY1305 => true,
            self::AES_256_CBC, self::AES_128_CBC => false,
        };
    }
}
