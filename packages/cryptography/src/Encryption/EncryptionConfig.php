<?php

namespace Tempest\Cryptography\Encryption;

final class EncryptionConfig
{
    /**
     * @param EncryptionAlgorithm $algorithm The algorithm used for encrypting and decrypting values.
     * @param null|non-empty-string $key A private, secure encryption key.
     */
    public function __construct(
        public EncryptionAlgorithm $algorithm,
        #[\SensitiveParameter]
        public readonly ?string $key,
    ) {}
}
